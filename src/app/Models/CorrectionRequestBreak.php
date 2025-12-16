<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CorrectionRequestBreak extends Model
{
    use HasFactory;

    public function correctionRequest(): BelongsTo
    {
        return $this->belongsTo(CorrectionRequest::class);
    }
}
