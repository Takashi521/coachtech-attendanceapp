<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CorrectionRequestBreak extends Model
{
    use HasFactory;

    protected $fillable = [
        'correction_request_id',
        'break_order',
        'requested_break_start_time',
        'requested_break_end_time',
    ];

    public function correctionRequest(): BelongsTo
    {
        return $this->belongsTo(CorrectionRequest::class);
    }
}
