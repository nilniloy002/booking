<x-admin>
    @section('title', 'Time Slots')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Time Slot List</h3>
            <div class="card-tools">
                <a href="{{ route('admin.time_slot.create') }}" class="btn btn-sm btn-info">New</a>
            </div>
        </div>
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Time Slot</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($timeSlots as $timeSlot)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $timeSlot->time_slot }}</td>
                            <td>{{ ucfirst($timeSlot->status) }}</td>
                            <td>
                                <a href="{{ route('admin.time_slot.edit', $timeSlot) }}" class="btn btn-sm btn-primary">Edit</a>
                                <form action="{{ route('admin.time_slot.destroy', $timeSlot) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Are you sure?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-admin>