<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    protected $fillable = [
        'campus_id', 'name', 'email', 'password',
        'role', 'department', 'position', 'is_active', 'signature_path',
    ];

    protected $hidden = ['password', 'remember_token'];
    protected $casts  = ['password' => 'hashed'];

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }

    public function submittedDocuments(): HasMany
    {
        return $this->hasMany(Document::class, 'submitted_by');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function assignedRoutes(): HasMany
    {
        return $this->hasMany(DocumentRoute::class, 'assigned_to');
    }

    // Role helpers
    public function isDocumentController(): bool { return $this->role === 'document_controller'; }
    public function isCampusDirector(): bool     { return $this->role === 'campus_director'; }
    public function isRoutingUnit(): bool         { return $this->role === 'routing_unit'; }
    public function isDocumentSubmitter(): bool   { return $this->role === 'document_submitter'; }
    public function canManageDocuments(): bool    { return in_array($this->role, ['document_controller', 'campus_director']); }

    public function getRoleLabel(): string
    {
        return match($this->role) {
            'document_controller' => 'Records Management Officer',
            'campus_director'     => 'Campus Director',
            'routing_unit'        => 'Routing Unit',
            'document_submitter'  => 'Document Submitter',
            default               => ucfirst($this->role),
        };
    }

    public function getRoleColor(): string
    {
        return match($this->role) {
            'document_controller' => 'danger',
            'campus_director'     => 'primary',
            'routing_unit'        => 'warning',
            'document_submitter'  => 'secondary',
            default               => 'secondary',
        };
    }
}
