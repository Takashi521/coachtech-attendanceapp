<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceBreak extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'break_order',
        'break_start_time',
        'break_end_time',
    ];

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }
}