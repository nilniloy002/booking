<?php

namespace App\Http\Controllers;

use App\Models\TimeSlot;
use Illuminate\Http\Request;

class TimeSlotController extends Controller
{
    public function index()
    {
        $timeSlots = TimeSlot::latest()->get();
        return view('admin.time_slot.index', compact('timeSlots'));
    }

    public function create()
    {
        $daysOfWeek = [
            'monday' => 'Monday',
            'tuesday' => 'Tuesday',
            'wednesday' => 'Wednesday',
            'thursday' => 'Thursday',
            'friday' => 'Friday',
            'saturday' => 'Saturday',
            'sunday' => 'Sunday'
        ];
        return view('admin.time_slot.create', compact('daysOfWeek'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'time_slot' => 'required|unique:time_slots|string|max:255',
            'status' => 'required|in:on,off',
            'day_of_week' => 'required|array|min:1',
            'day_of_week.*' => 'string|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday'
        ]);

        TimeSlot::create([
            'time_slot' => $request->time_slot,
            'status' => $request->status,
            'day_of_week' => $request->day_of_week
        ]);

        return redirect()->route('admin.time_slot.index')->with('success', 'Time slot created successfully.');
    }

    public function show(TimeSlot $timeSlot)
    {
        return view('admin.time_slot.show', compact('timeSlot'));
    }

    public function edit(TimeSlot $timeSlot)
    {
        $daysOfWeek = [
            'monday' => 'Monday',
            'tuesday' => 'Tuesday',
            'wednesday' => 'Wednesday',
            'thursday' => 'Thursday',
            'friday' => 'Friday',
            'saturday' => 'Saturday',
            'sunday' => 'Sunday'
        ];
        return view('admin.time_slot.edit', compact('timeSlot', 'daysOfWeek'));
    }

    public function update(Request $request, TimeSlot $timeSlot)
    {
        $request->validate([
            'time_slot' => 'required|string|max:255|unique:time_slots,time_slot,'.$timeSlot->id,
            'status' => 'required|in:on,off',
            'day_of_week' => 'required|array|min:1',
            'day_of_week.*' => 'string|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday'
        ]);

        $timeSlot->update([
            'time_slot' => $request->time_slot,
            'status' => $request->status,
            'day_of_week' => $request->day_of_week
        ]);

        return redirect()->route('admin.time_slot.index')->with('success', 'Time slot updated successfully.');
    }

    public function destroy(TimeSlot $timeSlot)
    {
        $timeSlot->delete();
        return redirect()->route('admin.time_slot.index')->with('success', 'Time slot deleted successfully.');
    }
}