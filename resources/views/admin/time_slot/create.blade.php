<x-admin>
    @section('title', 'Create Time Slot')
    <div class="card">
        <div class="card-header">Create Time Slot</div>
        <div class="card-body">
            <form action="{{ route('admin.time_slot.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label>Time Slot</label>
                    <input type="text" name="time_slot" class="form-control" required placeholder="Example: 10am-12pm">
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" class="form-control" required>
                        <option value="on">On</option>
                        <option value="off">Off</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Available Days</label>
                    <div class="row">
                        @foreach($daysOfWeek as $key => $day)
                            <div class="col-md-3">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="day_of_week[]" value="{{ $key }}">
                                        {{ $day }}
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <small class="form-text text-muted">Select at least one day when this time slot is available</small>
                </div>
                <button type="submit" class="btn btn-primary">Save</button>
            </form>
        </div>
    </div>
</x-admin>