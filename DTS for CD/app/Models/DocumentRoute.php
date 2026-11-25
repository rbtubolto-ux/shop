<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentRoute extends Model
{
    protected $fillable = [
        'document_id', 'workflow_step_id', 'step_order', 'step_name',
        'assigned_to', 'action', 'comments', 'acted_by', 'acted_at',
        'received_at', 'signature_path',
    ];

    protected $casts = [
        'acted_at'    => 'datetime',
        'received_at' => 'datetime',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function actedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acted_by');
    }

    public function workflowStep(): BelongsTo
    {
        return $this->belongsTo(WorkflowStep::class);
    }

    public function actionColor(): string
    {
        return match($this->action) {
            'Forwarded' => 'primary',
            'Endorsed'  => 'info',
            'Returned'  => 'danger',
            'Approved'  => 'success',
            'Noted'     => 'secondary',
            default     => 'light',
        };
    }
}
