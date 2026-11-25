<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecordsReview extends Model
{
    protected $fillable = ['campus_id', 'type', 'conducted_by', 'conducted_at', 'records_count', 'notes'];

    protected $casts = ['conducted_at' => 'datetime'];

    public function conductor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'conducted_by');
    }
}
