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
                <button type="submit" class="btn btn-primary">Update</button>
            </form>
        </div>
    </div>
</x-admin>