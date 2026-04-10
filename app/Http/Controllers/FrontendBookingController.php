<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Student;
use App\Models\TimeSlot;
use Illuminate\Http\Request;
use Carbon\Carbon;

class FrontendBookingController extends Controller
{
    public function welcome()
    {
        $timeSlots = TimeSlot::where('status', 'on')
            ->orderBy('time_slot', 'asc')
            ->get();
            
        return view('welcome', compact('timeSlots'));
    }

    public function checkSeatAvailability(Request $request)
    {
        $request->validate([
            'date' => 'required|date|after_or_equal:today'
        ]);

        try {
            $date = Carbon::parse($request->date);
            $dayOfWeek = strtolower($date->format('l')); // monday, tuesday, etc.
            $formattedDate = $date->format('Y-m-d');
            
            // Get all active time slots
            $allActiveSlots = TimeSlot::where('status', 'on')->get();
            
            // Filter time slots based on day availability
            $timeSlots = $allActiveSlots->filter(function($slot) use ($dayOfWeek) {
                // If no days specified, don't show the slot
                if (!$slot->day_of_week || empty($slot->day_of_week)) {
                    return false;
                }
                
                // Check if current day is in the available days array
                $availableDays = array_map('strtolower', $slot->day_of_week);
                return in_array($dayOfWeek, $availableDays);
            });

            if ($timeSlots->isEmpty()) {
                return response()->json([
                    'timeSlots' => [],
                    'date' => $formattedDate,
                    'message' => 'No time slots available for ' . ucfirst($dayOfWeek)
                ]);
            }

            // Get bookings and prepare response
            $bookings = Booking::where('date', $formattedDate)
                ->where('status', 'on')
                ->get(['time_slot_id', 'seat', 'std_id']);

            $responseData = $timeSlots->map(function($timeSlot) use ($bookings) {
                $slotBookings = $bookings->where('time_slot_id', $timeSlot->id);
                
                return [
                    'id' => $timeSlot->id,
                    'time_slot' => $this->formatTimeSlotDisplay($timeSlot->time_slot),
                    'available_seats' => 13 - $slotBookings->count(),
                    'bookings' => $slotBookings->map(function($booking) {
                        return [
                            'seat' => (int)$booking->seat,
                            'std_id' => $booking->std_id
                        ];
                    })->values()->toArray()
                ];
            });

            // Ensure we always return an array, not an object
            return response()->json([
                'timeSlots' => array_values($responseData->toArray()),
                'date' => $formattedDate
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'timeSlots' => [],
                'error' => 'An error occurred while fetching time slots: ' . $e->getMessage()
            ], 500);
        }
    }

    protected function formatTimeSlotDisplay($timeSlot)
    {
        // Convert formats like "10am-12pm" to "10:00 AM - 12:00 PM"
        if (preg_match('/^(\d{1,2})(am|pm)-(\d{1,2})(am|pm)$/i', $timeSlot, $matches)) {
            $startHour = $matches[1];
            $startPeriod = strtoupper($matches[2]);
            $endHour = $matches[3];
            $endPeriod = strtoupper($matches[4]);
            return "$startHour:00 $startPeriod - $endHour:00 $endPeriod";
        }
        return $timeSlot;
    }

    public function bookSeat(Request $request)
    {
        $request->validate([
            'date' => 'required|date|after_or_equal:today',
            'time_slot_id' => 'required|exists:time_slots,id',
            'seat' => 'required|integer|between:1,13',
            'std_id' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    if (!Student::where('std_id', $value)->exists()) {
                        $fail('The student ID does not exist in our records.');
                    }
                }
            ],
        ]);

        $bookingDate = Carbon::parse($request->date);
        $dayOfWeek = strtolower($bookingDate->format('l'));
        $formattedDate = $bookingDate->format('Y-m-d');
        
        // Check if time slot exists and is active
        $timeSlot = TimeSlot::find($request->time_slot_id);
        if (!$timeSlot || $timeSlot->status !== 'on') {
            return response()->json([
                'error' => 'The selected time slot is not available'
            ], 422);
        }

        // Check if time slot is available on this day of week
        if (!$timeSlot->day_of_week || empty($timeSlot->day_of_week)) {
            return response()->json([
                'error' => 'This time slot has no configured available days'
            ], 422);
        }

        $availableDays = array_map('strtolower', $timeSlot->day_of_week);
        if (!in_array($dayOfWeek, $availableDays)) {
            return response()->json([
                'error' => 'This time slot is not available on ' . ucfirst($dayOfWeek)
            ], 422);
        }

        // Check if seat is already booked
        $existingBooking = Booking::where('date', $formattedDate)
            ->where('time_slot_id', $request->time_slot_id)
            ->where('seat', $request->seat)
            ->where('status', 'on')
            ->exists();

        if ($existingBooking) {
            return response()->json([
                'error' => 'This seat has already been booked.'
            ], 422);
        }

        // Check if student already has a booking for this time slot
        $studentBooking = Booking::where('date', $formattedDate)
            ->where('time_slot_id', $request->time_slot_id)
            ->where('std_id', $request->std_id)
            ->where('status', 'on')
            ->exists();

        if ($studentBooking) {
            return response()->json([
                'error' => 'You already have a booking for this time slot.'
            ], 422);
        }

        // Check if student already has any booking on this day
        $studentDailyBooking = Booking::where('date', $formattedDate)
            ->where('std_id', $request->std_id)
            ->where('status', 'on')
            ->exists();

        if ($studentDailyBooking) {
            return response()->json([
                'error' => "You're already booked for a session on this day"
            ], 422);
        }

        // Create the booking
        try {
            $booking = Booking::create([
                'date' => $formattedDate,
                'time_slot_id' => $request->time_slot_id,
                'seat' => $request->seat,
                'std_id' => $request->std_id,
                'status' => 'on'
            ]);

            return response()->json([
                'success' => 'Seat booked successfully!',
                'booking' => $booking
            ]);
        } catch (\Exception $e) {
            // Check for duplicate entry error (student already booked on this day)
            if (str_contains($e->getMessage(), 'Duplicate entry') && str_contains($e->getMessage(), 'bookings_date_std_id_unique')) {
                return response()->json([
                    'error' => "You're already booked for a session on this day"
                ], 422);
            }
            
            return response()->json([
                'error' => 'Failed to create booking: ' . $e->getMessage()
            ], 500);
        }
    }

    public function checkStudentExists(Request $request)
    {
        $student = Student::where('std_id', $request->std_id)
            ->select('std_id', 'std_name', 'status')
            ->first();

        return response()->json([
            'exists' => $student !== null,
            'student_name' => $student ? $student->std_name : null,
            'status' => $student ? $student->status : null
        ]);
    }
}