<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkflowTemplate extends Model
{
    protected $fillable = ['campus_id', 'name', 'document_type', 'description', 'is_active'];

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }

    public function steps(): HasMany
    {
        return $this->hasMany(WorkflowStep::class)->orderBy('step_order');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }
}
