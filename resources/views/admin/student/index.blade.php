<x-admin>
    @section('title', 'Students')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Student List</h3>
            <div class="card-tools">
                <a href="{{ route('admin.student.create') }}" class="btn btn-sm btn-info">New</a>
            </div>
        </div>
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Photo</th>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Batch</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($students as $student)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                @if($student->std_image)
                                    <img src="{{ asset('storage/' . $student->std_image) }}" alt="" height="40">
                                @else
                                    <span class="text-muted">No image</span>
                                @endif
                            </td>
                            <td>{{ $student->std_id }}</td>
                            <td>{{ $student->std_name }}</td>
                            <td>{{ $student->std_email }}</td>
                            <td>{{ $student->std_batch }}</td>
                            <td>{{ ucfirst($student->status) }}</td>
                            <td>
                                <a href="{{ route('admin.student.edit', $student) }}" class="btn btn-sm btn-primary">Edit</a>
                                <form action="{{ route('admin.student.destroy', $student) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Are you sure?')">
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