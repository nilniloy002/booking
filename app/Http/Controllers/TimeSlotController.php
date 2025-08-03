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
        return view('admin.time_slot.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'time_slot' => 'required|unique:time_slots|string|max:255',
            'status' => 'required|in:on,off',
        ]);

        TimeSlot::create($request->only(['time_slot', 'status']));

        return redirect()->route('admin.time_slot.index')->with('success', 'Time slot created successfully.');
    }

    public function show(TimeSlot $timeSlot)
    {
        return view('admin.time_slot.show', compact('timeSlot'));
    }

    public function edit(TimeSlot $timeSlot)
    {
        return view('admin.time_slot.edit', compact('timeSlot'));
    }

    public function update(Request $request, TimeSlot $timeSlot)
    {
        $request->validate([
            'time_slot' => 'required|string|max:255|unique:time_slots,time_slot,'.$timeSlot->id,
            'status' => 'required|in:on,off',
        ]);

        $timeSlot->update($request->only(['time_slot', 'status']));

        return redirect()->route('admin.time_slot.index')->with('success', 'Time slot updated successfully.');
    }

    public function destroy(TimeSlot $timeSlot)
    {
        $timeSlot->delete();
        return redirect()->route('admin.time_slot.index')->with('success', 'Time slot deleted successfully.');
    }
}