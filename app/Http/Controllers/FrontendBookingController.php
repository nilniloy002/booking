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
            $dayOfWeek = $date->dayOfWeek;
            
            // Check if date is Friday or Saturday
            if ($dayOfWeek === Carbon::FRIDAY || $dayOfWeek === Carbon::SATURDAY) {
                return response()->json([
                    'timeSlots' => [],
                    'date' => $date->format('Y-m-d'),
                    'message' => 'Booking is not available on Fridays and Saturdays'
                ]);
            }

            $formattedDate = $date->format('Y-m-d');
            
            // Get active time slots
            $timeSlots = TimeSlot::where('status', 'on')
                ->orderBy('time_slot', 'asc')
                ->get(['id', 'time_slot']);

            if ($timeSlots->isEmpty()) {
                return response()->json([
                    'timeSlots' => [],
                    'date' => $formattedDate,
                    'message' => 'No active time slots available'
                ]);
            }

            // Check if it's TUESDAY, SUNDAY and disable 11am-1pm slots
            // if ($dayOfWeek === Carbon::TUESDAY || $dayOfWeek === Carbon::SUNDAY) {
            if ($dayOfWeek === Carbon::TUESDAY) {
                $timeSlots = $timeSlots->filter(function($slot) {
                    // return !in_array($slot->time_slot, ['11am-12pm', '12pm-1pm']);
                    return !in_array($slot->time_slot, ['11am-1pm','1pm-3pm']);
                });
                
                if ($timeSlots->isEmpty()) {
                    return response()->json([
                        'timeSlots' => [],
                        'date' => $formattedDate,
                        'message' => 'No time slots available on TUESDAY between 11 AM to 3 PM'
                    ]);
                }
            }

            // Check if it's WEDNESDAY and disable 1pm-3pm slots
            if ($dayOfWeek === Carbon::WEDNESDAY) {
                $timeSlots = $timeSlots->filter(function($slot) {
                    return !in_array($slot->time_slot, ['1pm-3pm','3pm-5pm']);
                });
                
                if ($timeSlots->isEmpty()) {
                    return response()->json([
                        'timeSlots' => [],
                        'date' => $formattedDate,
                        'message' => 'No time slots available on WEDNESDAY between 1 PM to 5 PM'
                    ]);
                }
            }


            // Check if it's THURSDAY and Available 3pm-5pm slots
            if ($dayOfWeek === Carbon::THURSDAY) {
                $timeSlots = $timeSlots->filter(function($slot) {
                    return !in_array($slot->time_slot, ['3pm-5pm']);
                });
                
                if ($timeSlots->isEmpty()) {
                    return response()->json([
                        'timeSlots' => [],
                        'date' => $formattedDate,
                        'message' => 'No time slots available on THURSDAY between 3PM to 5PM'
                    ]);
                }
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
                    'available_seats' => 15 - $slotBookings->count(),
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
                'error' => 'An error occurred while fetching time slots'
            ], 500);
        }
    }

    protected function formatTimeSlotDisplay($timeSlot)
    {
        // Convert formats like "1pm-2pm" to "1:00 PM - 2:00 PM"
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
        'seat' => 'required|integer|between:1,15',
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
    $dayOfWeek = $bookingDate->dayOfWeek; // Define this variable
    
    // Check if date is Friday or Saturday
    if ($dayOfWeek === Carbon::FRIDAY || $dayOfWeek === Carbon::SATURDAY) {
        return response()->json([
            'error' => 'Booking is not available on Fridays and Saturdays'
        ], 422);
    }

    // Check if it's TUESDAY,SUNDAY and trying to book restricted slots
    // if ($dayOfWeek === Carbon::TUESDAY || $dayOfWeek === Carbon::SUNDAY) {
    if ($dayOfWeek === Carbon::TUESDAY) {
        $timeSlot = TimeSlot::find($request->time_slot_id);
        if (in_array($timeSlot->time_slot, ['11am-1pm','1pm-3pm'])) {
            return response()->json([
                'error' => 'Booking is not available on TUESDAY between 11 AM to 3 PM'
            ], 422);
        }
    }

    // Check if it's WEDNESDAY and trying to book restricted slots
    if ($dayOfWeek === Carbon::WEDNESDAY ) {
        $timeSlot = TimeSlot::find($request->time_slot_id);
        if (in_array($timeSlot->time_slot, ['1pm-3pm','3pm-5pm'])) {
            return response()->json([
                'error' => 'Booking is not available on WEDNESDAY between 1 PM to 5 PM'
            ], 422);
        }
    }
    
    // Check if it's THURSDAY and trying to book restricted slots
    if ($dayOfWeek === Carbon::THURSDAY ) {
        $timeSlot = TimeSlot::find($request->time_slot_id);
        if (in_array($timeSlot->time_slot, ['3pm-5pm'])) {
            return response()->json([
                'error' => 'Booking is not available on THURSDAY between 3PM to 5PM'
            ], 422);
        }
        }

        // Check if time slot is active
        $timeSlot = TimeSlot::find($request->time_slot_id);
        if (!$timeSlot || $timeSlot->status !== 'on') {
            return response()->json([
                'error' => 'The selected time slot is not available'
            ], 422);
        }

        // Check if seat is already booked
        $existingBooking = Booking::where('date', $request->date)
            ->where('time_slot_id', $request->time_slot_id)
            ->where('seat', $request->seat)
            ->where('status', 'on')
            ->exists();

        if ($existingBooking) {
            return response()->json([
                'error' => 'This seat has already been booked.'
            ], 422);
        }

        // Check if student already has a booking
        $studentBooking = Booking::where('date', $request->date)
            ->where('time_slot_id', $request->time_slot_id)
            ->where('std_id', $request->std_id)
            ->where('status', 'on')
            ->exists();

        if ($studentBooking) {
            return response()->json([
                'error' => 'You already have a booking for this time slot.'
            ], 422);
        }

        // Create the booking
        try {
           // Create the booking
        $booking = Booking::create([
            'date' => $request->date,
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
        // Check for duplicate entry error
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