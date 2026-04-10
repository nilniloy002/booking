<x-admin>
    @section('title', 'Edit Time Slot')
    <div class="card">
        <div class="card-header">Edit Time Slot</div>
        <div class="card-body">
            <form action="{{ route('admin.time_slot.update', $timeSlot) }}" method="POST">
                @csrf @method('PUT')
                <div class="form-group">
                    <label>Time Slot</label>
                    <input type="text" name="time_slot" class="form-control" value="{{ $timeSlot->time_slot }}" required>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" class="form-control" required>
                        <option value="on" {{ $timeSlot->status === 'on' ? 'selected' : '' }}>On</option>
                        <option value="off" {{ $timeSlot->status === 'off' ? 'selected' : '' }}>Off</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Available Days</label>
                    <div class="row">
                        @foreach($daysOfWeek as $key => $day)
                            <div class="col-md-3">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="day_of_week[]" value="{{ $key }}"
                                            {{ is_array($timeSlot->day_of_week) && in_array($key, $timeSlot->day_of_week) ? 'checked' : '' }}>
                                        {{ $day }}
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <small class="form-text text-muted">Select at least one day when this time slot is available</small>
                </div>
                <button type="submit" class="btn btn-primary">Update</button>
            </form>
        </div>
    </div>
</x-admin>