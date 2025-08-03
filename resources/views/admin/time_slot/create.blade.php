<x-admin>
    @section('title', 'Create Time Slot')
    <div class="card">
        <div class="card-header">Create Time Slot</div>
        <div class="card-body">
            <form action="{{ route('admin.time_slot.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label>Time Slot</label>
                    <input type="text" name="time_slot" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" class="form-control" required>
                        <option value="on">On</option>
                        <option value="off">Off</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Save</button>
            </form>
        </div>
    </div>
</x-admin>