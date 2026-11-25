<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowStep extends Model
{
    // Subjects from the routing slip (form PSHS-00-F-RMU-01).
    public const SUBJECTS = [
        'Action', 'Approval', 'Comment', 'Discuss with me', 'Endorsement', 'File', 'Give Status',
        'Handle', 'Information', 'Initial / Clearance', 'Note & File', 'Note & Return',
        'Prepare Draft / Reply', 'Review',
    ];

    protected $fillable = [
        'workflow_template_id', 'step_order', 'step_name',
        'assigned_to', 'role_required', 'subject', 'pending_threshold_hours',
    ];

    // Subject used when a step has none chosen: the usual action for that role.
    public static function defaultSubjectFor(?string $role): ?string
    {
        return match ($role) {
            'routing_unit'      => 'Action',
            'campus_director'   => 'Approval',
            'document_controller' => 'Review',
            default             => null,
        };
    }

    public function workflowTemplate(): BelongsTo
    {
        return $this->belongsTo(WorkflowTemplate::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
