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
            <!-- Filter Form -->
            <form method="GET" action="{{ route('admin.booking.index') }}" class="mb-4">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="date">Date</label>
                            <input type="date" name="date" id="date" class="form-control" 
                                   value="{{ request('date') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="time_slot_id">Time Slot</label>
                            <select name="time_slot_id" id="time_slot_id" class="form-control">
                                <option value="">All Time Slots</option>
                                @foreach($timeSlots as $timeSlot)
                                    <option value="{{ $timeSlot->id }}" 
                                        {{ request('time_slot_id') == $timeSlot->id ? 'selected' : '' }}>
                                        {{ $timeSlot->time_slot }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="std_id">Student ID</label>
                            <input type="text" name="std_id" id="std_id" class="form-control" 
                                   value="{{ request('std_id') }}" placeholder="Enter Student ID">
                        </div>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Filter</button>
                            <a href="{{ route('admin.booking.index') }}" class="btn btn-secondary ml-2">Reset</a>
                        </div>
                    </div>
                </div>
            </form>
            
            <!-- Bookings Table -->
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
                    @forelse($bookings as $booking)
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
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">No bookings found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-admin>