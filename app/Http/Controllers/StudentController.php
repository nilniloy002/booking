<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Imports\StudentsImport;
use Maatwebsite\Excel\Facades\Excel;

class StudentController extends Controller
{
    public function index()
    {
        $students = Student::latest()->get();
        return view('admin.student.index', compact('students'));
    }

    public function create()
    {
        return view('admin.student.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'std_id' => 'required|unique:students|string|max:50',
            'std_name' => 'required|string|max:255',
            'std_email' => 'required|email|unique:students',
            'std_mobile' => 'required|numeric',
            'std_batch' => 'required|string|max:255',
            'std_image' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
            'status' => 'required|in:on,off',
        ]);

        $imagePath = $request->file('std_image')->store('student-images', 'public');

        Student::create([
            'std_id' => $request->std_id,
            'std_name' => $request->std_name,
            'std_email' => $request->std_email,
            'std_mobile' => $request->std_mobile,
            'std_batch' => $request->std_batch,
            'std_image' => $imagePath,
            'status' => $request->status,
        ]);

        return redirect()->route('admin.student.index')->with('success', 'Student created successfully.');
    }

    public function show(Student $student)
    {
        return view('admin.student.show', compact('student'));
    }

    public function edit(Student $student)
    {
        return view('admin.student.edit', compact('student'));
    }

    public function update(Request $request, Student $student)
    {
        $request->validate([
            'std_id' => 'required|string|max:50|unique:students,std_id,'.$student->id,
            'std_name' => 'required|string|max:255',
            'std_email' => 'required|email|unique:students,std_email,'.$student->id,
            'std_mobile' => 'required|numeric',
            'std_batch' => 'required|string|max:255',
            'std_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'status' => 'required|in:on,off',
        ]);

        $data = $request->only(['std_id', 'std_name', 'std_email', 'std_mobile', 'std_batch', 'status']);

        if ($request->hasFile('std_image')) {
            // Delete old image
            if (Storage::disk('public')->exists($student->std_image)) {
                Storage::disk('public')->delete($student->std_image);
            }
            
            $imagePath = $request->file('std_image')->store('student-images', 'public');
            $data['std_image'] = $imagePath;
        }

        $student->update($data);

        return redirect()->route('admin.student.index')->with('success', 'Student updated successfully.');
    }

    public function destroy(Student $student)
    {
        if (Storage::disk('public')->exists($student->std_image)) {
            Storage::disk('public')->delete($student->std_image);
        }
        
        $student->delete();
        return redirect()->route('admin.student.index')->with('success', 'Student deleted successfully.');
    }

    public function importForm()
    {
        return view('admin.student.import');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        try {
            Excel::import(new StudentsImport, $request->file('file'));
            
            return redirect()->route('admin.student.index')
                ->with('success', 'Students imported successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error importing students: ' . $e->getMessage());
        }
    }
}