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

// public function checkSeatAvailability(Request $request)
//     {
//         $request->validate([
//             'date' => 'required|date|after_or_equal:today'
//         ]);

//         try {
//             $date = Carbon::parse($request->date)->format('Y-m-d');
            
//             // Get all active time slots
//             $timeSlots = TimeSlot::where('status', 'on')
//                 ->orderBy('time_slot', 'asc')
//                 ->get(['id', 'time_slot']);

//             if ($timeSlots->isEmpty()) {
//                 return response()->json([
//                     'timeSlots' => [],
//                     'date' => $date
//                 ]);
//             }

//             // Get all bookings for the selected date
//             $bookings = Booking::where('date', $date)
//                 ->where('status', 'on')
//                 ->get(['time_slot_id', 'seat', 'std_id']);

//             // Prepare response data with guaranteed array structure
//             $responseData = $timeSlots->map(function($timeSlot) use ($bookings) {
//                 $slotBookings = $bookings->where('time_slot_id', $timeSlot->id);
                
//                 return [
//                     'id' => $timeSlot->id,
//                     'time_slot' => $timeSlot->time_slot,
//                     'available_seats' => 15 - $slotBookings->count(),
//                     'bookings' => $slotBookings->map(function($booking) {
//                         return [
//                             'seat' => (int)$booking->seat,
//                             'std_id' => $booking->std_id
//                         ];
//                     })->values()->toArray() // Ensure sequential array
//                 ];
//             });

//             return response()->json([
//                 'timeSlots' => $responseData,
//                 'date' => $date
//             ]);

//         } catch (\Exception $e) {
//             return response()->json([
//                 'error' => 'An error occurred while fetching time slots'
//             ], 500);
//         }
//     }

public function checkSeatAvailability(Request $request)
{
    $request->validate([
        'date' => 'required|date|after_or_equal:today'
    ]);

    try {
        $date = Carbon::parse($request->date);
        $dayOfWeek = $date->dayOfWeek; // 0 (Sunday) to 6 (Saturday)
        
        // Check if date is Friday (5) or Saturday (6)
        if ($dayOfWeek === Carbon::FRIDAY || $dayOfWeek === Carbon::SATURDAY) {
            return response()->json([
                'timeSlots' => [],
                'date' => $date->format('Y-m-d'),
                'message' => 'Booking is not available on Fridays and Saturdays'
            ]);
        }

        $formattedDate = $date->format('Y-m-d');
        
        // Get only active time slots (status = 'on')
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

        // Get all bookings for the selected date
        $bookings = Booking::where('date', $formattedDate)
            ->where('status', 'on')
            ->get(['time_slot_id', 'seat', 'std_id']);

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
                })->values()->toArray()
            ];
        });

        return response()->json([
            'timeSlots' => $responseData,
            'date' => $formattedDate
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

    // Parse the date and check if it's Friday (5) or Saturday (6)
    $bookingDate = Carbon::parse($request->date);
    if ($bookingDate->dayOfWeek === Carbon::FRIDAY || $bookingDate->dayOfWeek === Carbon::SATURDAY) {
        return response()->json([
            'error' => 'Booking is not available on Fridays and Saturdays'
        ], 422);
    }

    // Check if time slot is active
    $timeSlot = TimeSlot::find($request->time_slot_id);
    if (!$timeSlot || $timeSlot->status !== 'on') {
        return response()->json([
            'error' => 'The selected time slot is not available'
        ], 422);
    }

    // Check if seat is already booked for this timeslot
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

    // Check if student already has a booking for this timeslot
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
        return response()->json([
            'error' => 'An error occurred while processing your booking. Please try again or you are already booked for this date'
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
