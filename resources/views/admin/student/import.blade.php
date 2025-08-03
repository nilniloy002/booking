<x-admin>
    @section('title', 'Import Students')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Import Students</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.student.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <label>Select File (Excel/CSV)</label>
                    <input type="file" name="file" class="form-control" required accept=".xlsx,.xls,.csv">
                    <small class="form-text text-muted">
                        Download <a href="{{ asset('sample/student_import_sample.xlsx') }}">sample file</a> for reference.
                    </small>
                </div>
                <button type="submit" class="btn btn-primary">Import</button>
                <a href="{{ route('admin.student.index') }}" class="btn btn-default">Cancel</a>
            </form>
        </div>
    </div>
</x-admin>