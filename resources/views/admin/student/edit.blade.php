<x-admin>
    @section('title', 'Edit Student')
    <div class="card">
        <div class="card-header">Edit Student</div>
        <div class="card-body">
            <form action="{{ route('admin.student.update', $student) }}" method="POST" enctype="multipart/form-data">
                @csrf @method('PUT')
                <div class="form-group">
                    <label>Student ID</label>
                    <input type="text" name="std_id" class="form-control" value="{{ $student->std_id }}" required>
                </div>
                <div class="form-group">
                    <label>Name</label>
                    <input type="text" name="std_name" class="form-control" value="{{ $student->std_name }}" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="std_email" class="form-control" value="{{ $student->std_email }}" required>
                </div>
                <div class="form-group">
                    <label>Mobile</label>
                    <input type="text" name="std_mobile" class="form-control" value="{{ $student->std_mobile }}" required>
                </div>
                <div class="form-group">
                    <label>Batch</label>
                    <input type="text" name="std_batch" class="form-control" value="{{ $student->std_batch }}" required>
                </div>
                <div class="form-group">
                    <label>Photo</label>
                    <input type="file" name="std_image" class="form-control">
                    @if($student->std_image)
                        <small>Current: <img src="{{ asset('storage/' . $student->std_image) }}" height="40"></small>
                    @endif
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" class="form-control" required>
                        <option value="on" {{ $student->status === 'on' ? 'selected' : '' }}>Active</option>
                        <option value="off" {{ $student->status === 'off' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Update</button>
            </form>
        </div>
    </div>
</x-admin>