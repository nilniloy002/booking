<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\TimeSlot;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
   public function index(Request $request)
    {
        $query = Booking::with('timeSlot')->latest();
        
        // Date filter
        if ($request->has('date') && !empty($request->date)) {
            $query->where('date', $request->date);
        }
        
        // Time Slot filter
        if ($request->has('time_slot_id') && !empty($request->time_slot_id)) {
            $query->where('time_slot_id', $request->time_slot_id);
        }
        
        // Student ID filter
        if ($request->has('std_id') && !empty($request->std_id)) {
            $query->where('std_id', 'like', '%'.$request->std_id.'%');
        }
        
        $bookings = $query->get();
        $timeSlots = TimeSlot::where('status', 'on')->get();
        
        return view('admin.booking.index', compact('bookings', 'timeSlots'));
    }

    public function create()
    {
        $timeSlots = TimeSlot::where('status', 'on')->get();
        return view('admin.booking.create', compact('timeSlots'));
    }

    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'date' => 'required|date',
    //         'time_slot_id' => 'required|exists:time_slots,id',
    //         'seat' => 'required|integer|between:1,15',
    //         'std_id' => 'required|string',
    //     ]);

    //     DB::transaction(function () use ($request) {
    //         // Check if seat is available
    //         $existingBooking = Booking::where('date', $request->date)
    //             ->where('time_slot_id', $request->time_slot_id)
    //             ->where('seat', $request->seat)
    //             ->exists();

    //         if ($existingBooking) {
    //             throw new \Exception('This seat is already booked for the selected time slot');
    //         }

    //         // Check if student already has a booking for this date
    //         $studentBooking = Booking::where('date', $request->date)
    //             ->where('std_id', $request->std_id)
    //             ->exists();

    //         if ($studentBooking) {
    //             throw new \Exception('This student already has a booking for this date');
    //         }

    //         Booking::create([
    //             'date' => $request->date,
    //             'time_slot_id' => $request->time_slot_id,
    //             'seat' => $request->seat,
    //             'std_id' => $request->std_id,
    //             'status' => 'on'
    //         ]);
    //     });

    //     return redirect()->route('admin.booking.index')->with('success', 'Booking created successfully.');
    // }


    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'time_slot_id' => 'required|exists:time_slots,id',
            'seat' => 'required|integer|between:1,15',
            // 'std_id' => 'required|string',

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

        try {
            DB::transaction(function () use ($request) {
                // Check if seat is available
                $existingBooking = Booking::where('date', $request->date)
                    ->where('time_slot_id', $request->time_slot_id)
                    ->where('seat', $request->seat)
                    ->exists();

                if ($existingBooking) {
                    throw new \Exception('This seat is already booked for the selected time slot');
                }

                // Check if student already has a booking for this date
                $studentBooking = Booking::where('date', $request->date)
                    ->where('std_id', $request->std_id)
                    ->exists();

                if ($studentBooking) {
                    throw new \Exception('This student already has a booking for this date');
                }

                Booking::create([
                    'date' => $request->date,
                    'time_slot_id' => $request->time_slot_id,
                    'seat' => $request->seat,
                    'std_id' => $request->std_id,
                    'status' => 'on'
                ]);
            });
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.booking.index')->with('success', 'Booking created successfully.');
    }

    public function show(Booking $booking)
    {
        return view('admin.booking.show', compact('booking'));
    }

    // public function edit(Booking $booking)
    // {
    //     $timeSlots = TimeSlot::where('status', 'on')->get();
    //     return view('admin.booking.edit', compact('booking', 'timeSlots'));
    // }

    // public function update(Request $request, Booking $booking)
    // {
    //     $request->validate([
    //         'date' => 'required|date',
    //         'time_slot_id' => 'required|exists:time_slots,id',
    //         'seat' => 'required|integer|between:1,15',
    //         'std_id' => 'required|string',
    //         'status' => 'required|in:on,off',
    //     ]);

    //     DB::transaction(function () use ($request, $booking) {
    //         // Similar validation as store but exclude current booking
    //         $existingBooking = Booking::where('date', $request->date)
    //             ->where('time_slot_id', $request->time_slot_id)
    //             ->where('seat', $request->seat)
    //             ->where('id', '!=', $booking->id)
    //             ->exists();

    //         if ($existingBooking) {
    //             throw new \Exception('This seat is already booked for the selected time slot');
    //         }

    //         $studentBooking = Booking::where('date', $request->date)
    //             ->where('std_id', $request->std_id)
    //             ->where('id', '!=', $booking->id)
    //             ->exists();

    //         if ($studentBooking) {
    //             throw new \Exception('This student already has a booking for this date');
    //         }

    //         $booking->update($request->all());
    //     });

    //     return redirect()->route('admin.booking.index')->with('success', 'Booking updated successfully.');
    // }

    public function edit(Booking $booking)
    {
        $timeSlots = TimeSlot::where('status', 'on')->get();
        $bookedSeats = Booking::where('date', $booking->date)
            ->where('time_slot_id', $booking->time_slot_id)
            ->where('id', '!=', $booking->id)
            ->pluck('seat')
            ->toArray();
        
        // Get student name for display
        $student = Student::where('std_id', $booking->std_id)->first();
        
        return view('admin.booking.edit', compact('booking', 'timeSlots', 'bookedSeats', 'student'));
    }

    public function update(Request $request, Booking $booking)
    {
        $request->validate([
            'date' => 'required|date',
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
            'status' => 'required|in:on,off',
        ]);

        try {
            DB::transaction(function () use ($request, $booking) {
                // Check if seat is available (excluding current booking)
                $existingBooking = Booking::where('date', $request->date)
                    ->where('time_slot_id', $request->time_slot_id)
                    ->where('seat', $request->seat)
                    ->where('id', '!=', $booking->id)
                    ->exists();

                if ($existingBooking) {
                    throw new \Exception('This seat is already booked for the selected time slot');
                }

                // Check if student already has another booking for this date
                $studentBooking = Booking::where('date', $request->date)
                    ->where('std_id', $request->std_id)
                    ->where('id', '!=', $booking->id)
                    ->exists();

                if ($studentBooking) {
                    throw new \Exception('This student already has a booking for this date');
                }

                $booking->update($request->all());
            });
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.booking.index')->with('success', 'Booking updated successfully.');
    }

    public function destroy(Booking $booking)
    {
        $booking->delete();
        return redirect()->route('admin.booking.index')->with('success', 'Booking deleted successfully.');
    }

public function checkStudentBooking(Request $request)
{
    $query = Booking::where('date', $request->date)
        ->where('std_id', $request->std_id);
        
    if ($request->has('exclude_id')) {
        $query->where('id', '!=', $request->exclude_id);
    }
    
    $booked = $query->exists();

    return response()->json(['booked' => $booked]);
}

public function getSeatAvailability(Request $request)
{
    $query = Booking::where('date', $request->date)
        ->where('time_slot_id', $request->time_slot_id);
        
    if ($request->has('exclude_id')) {
        $query->where('id', '!=', $request->exclude_id);
    }
    
    $bookedSeats = $query->pluck('seat')->toArray();
    $allSeats = range(1, 15);
    $availableSeats = array_diff($allSeats, $bookedSeats);

    return response()->json([
        'bookedSeats' => $bookedSeats,
        'availableSeats' => $availableSeats
    ]);
}

public function checkStudentExists(Request $request)
{
    $student = Student::where('std_id', $request->std_id)->first();
    
    return response()->json([
        'exists' => $student !== null,
        'student_name' => $student ? $student->std_name : null,
        'status' => $student ? $student->status : null
    ]);
}

    
}