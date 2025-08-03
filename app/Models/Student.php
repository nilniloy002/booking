<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $table = 'students';

    protected $fillable = [
        'std_id',
        'std_name',
        'std_image',
        'std_email',
        'std_mobile',
        'std_batch',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
    ];
}