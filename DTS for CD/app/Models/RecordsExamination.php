<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecordsExamination extends Model
{
    protected $fillable = ['document_id', 'condition', 'notes', 'examined_by', 'examined_at'];

    protected $casts = ['examined_at' => 'datetime'];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function examiner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'examined_by');
    }
}
