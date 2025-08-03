<x-admin>
    @section('title', 'Bookings')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Booking List</h3>
            <div class="card-tools">
                <a href="{{ route('admin.booking.create') }}" class="btn btn-sm btn-info">New</a>
            </div>
        </div>
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Date</th>
                        <th>Time Slot</th>
                        <th>Seat</th>
                        <th>Student ID</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($bookings as $booking)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $booking->date->format('Y-m-d') }}</td>
                            <td>{{ $booking->timeSlot->time_slot }}</td>
                            <td>{{ $booking->seat }}</td>
                            <td>{{ $booking->std_id }}</td>
                            <td>{{ ucfirst($booking->status) }}</td>
                            <td>
                                <a href="{{ route('admin.booking.edit', $booking) }}" class="btn btn-sm btn-primary">Edit</a>
                                <form action="{{ route('admin.booking.destroy', $booking) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Are you sure?')">
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