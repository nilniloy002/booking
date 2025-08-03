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
            $date = Carbon::parse($request->date)->format('Y-m-d');
            
            // Get all active time slots
            $timeSlots = TimeSlot::where('status', 'on')
                ->orderBy('time_slot', 'asc')
                ->get(['id', 'time_slot']);

            if ($timeSlots->isEmpty()) {
                return response()->json([
                    'timeSlots' => [],
                    'date' => $date
                ]);
            }

            // Get all bookings for the selected date
            $bookings = Booking::where('date', $date)
                ->where('status', 'on')
                ->get(['time_slot_id', 'seat', 'std_id']);

            // Prepare response data with guaranteed array structure
            $responseData = $timeSlots->map(function($timeSlot) use ($bookings) {
                $slotBookings = $bookings->where('time_slot_id', $timeSlot->id);
                
                return [
                    'id' => $timeSlot->id,
                    'time_slot' => $timeSlot->time_slot,
                    'available_seats' => 15 - $slotBookings->count(),
                    'bookings' => $slotBookings->map(function($booking) {
                        return [
                            'seat' => (int)$booking->seat,
                            'std_id' => $booking->std_id
                        ];
                    })->values()->toArray() // Ensure sequential array
                ];
            });

            return response()->json([
                'timeSlots' => $responseData,
                'date' => $date
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred while fetching time slots'
            ], 500);
        }
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

        // Check if seat is available
        $existingBooking = Booking::where('date', $request->date)
            ->where('time_slot_id', $request->time_slot_id)
            ->where('seat', $request->seat)
            ->exists();

        if ($existingBooking) {
            return response()->json(['error' => 'This seat has already been booked.'], 422);
        }

        // Check if student already has a booking for this timeslot
        $studentBooking = Booking::where('date', $request->date)
            ->where('time_slot_id', $request->time_slot_id)
            ->where('std_id', $request->std_id)
            ->exists();

        if ($studentBooking) {
            return response()->json(['error' => 'You already have a booking for this time slot.'], 422);
        }

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
