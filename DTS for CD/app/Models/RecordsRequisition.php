<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecordsRequisition extends Model
{
    protected $fillable = [
        'document_id', 'requested_by', 'purpose', 'status',
        'issued_by', 'issued_at', 'acknowledged_at', 'returned_at',
    ];

    protected $casts = [
        'issued_at'       => 'datetime',
        'acknowledged_at' => 'datetime',
        'returned_at'     => 'datetime',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }
}
