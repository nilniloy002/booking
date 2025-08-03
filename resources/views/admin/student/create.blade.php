<x-admin>
    @section('title', 'Create Student')
    <div class="card">
        <div class="card-header">Create Student</div>
        <div class="card-body">
            <form action="{{ route('admin.student.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <label>Student ID</label>
                    <input type="text" name="std_id" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Name</label>
                    <input type="text" name="std_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="std_email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Mobile</label>
                    <input type="text" name="std_mobile" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Batch</label>
                    <input type="text" name="std_batch" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Photo</label>
                    <input type="file" name="std_image" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" class="form-control" required>
                        <option value="on">Active</option>
                        <option value="off">Inactive</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Save</button>
            </form>
        </div>
    </div>
</x-admin>