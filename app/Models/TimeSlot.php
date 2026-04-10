<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimeSlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'time_slot',
        'status',
        'day_of_week'
    ];

    protected $casts = [
        'status' => 'string',
        'day_of_week' => 'array'
    ];

    // Helper method to check if time slot is available on a specific day
    public function isAvailableOnDay($dayOfWeek)
    {
        if (!$this->day_of_week || $this->status !== 'on') {
            return false;
        }
        
        $days = array_map('strtolower', $this->day_of_week);
        return in_array(strtolower($dayOfWeek), $days);
    }

    // Get formatted days for display
    public function getFormattedDaysAttribute()
    {
        if (!$this->day_of_week) {
            return 'Not set';
        }
        
        $days = array_map('ucfirst', $this->day_of_week);
        return implode(', ', $days);
    }
}