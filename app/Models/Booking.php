<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'time_slot_id',
        'seat',
        'std_id',
        'status'
    ];

    protected $casts = [
        'date' => 'date',
        'status' => 'string'
    ];

    public function timeSlot()
    {
        return $this->belongsTo(TimeSlot::class);
    }
}