<?php

namespace App\Imports;

use App\Models\Student;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StudentsImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row)
    {
        // Handle image if present in the import
        $imagePath = null;
        if (isset($row['image']) && !empty($row['image'])) {
            // This assumes the image is a URL in the CSV
            // You might need to adjust based on your actual import needs
            $imageContents = file_get_contents($row['image']);
            $filename = Str::random(20) . '.jpg'; // or extract extension
            $imagePath = 'student-images/' . $filename;
            Storage::disk('public')->put($imagePath, $imageContents);
        }

        return new Student([
            'std_id'     => $row['student_id'],
            'std_name'   => $row['name'],
            'std_email'  => $row['email'],
            'std_mobile' => $row['mobile'],
            'std_batch'  => $row['batch'],
            'std_image'  => $imagePath,
            'status'     => $row['status'] ?? 'on',
        ]);
    }

    public function rules(): array
    {
        return [
            'student_id' => 'required|unique:students,std_id',
            'name' => 'required',
            'email' => 'required|email|unique:students,std_email',
            'mobile' => 'required',
            'batch' => 'required',
            'status' => 'nullable|in:on,off',
        ];
    }
}