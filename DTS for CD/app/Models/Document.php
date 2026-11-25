<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Document extends Model
{
    protected $fillable = [
        'campus_id', 'doc_number', 'barcode', 'title', 'category', 'direction',
        'description', 'status', 'originating_office', 'signatory',
        'date_submitted', 'due_date', 'file_path', 'file_name',
        'submitted_by', 'workflow_template_id', 'current_step',
        'cabinet', 'drawer', 'container', 'remarks', 'archived_at',
        'signature_path', 'approved_by', 'approved_at',
        'retention_code', 'is_confidential', 'disposition',
    ];

    protected $casts = [
        'date_submitted' => 'date',
        'due_date'       => 'date',
        'archived_at'    => 'datetime',
        'is_confidential' => 'boolean',
        'approved_at'    => 'datetime',
    ];

    public const CATEGORIES = [
        'Memorandum', 'Request', 'Endorsement', 'Clearance',
        'Budget Proposal', 'Leave Request', 'Special Order',
        'Travel Order', 'MANCOM Resolution', 'BOT Resolution', 'Others',
    ];

    public const STATUSES = [
        'Draft', 'Submitted', 'In Review', 'Forwarded',
        'Endorsed', 'For Approval', 'Approved', 'Returned', 'Archived',
    ];

    public const STATUS_COLORS = [
        'Draft'       => 'secondary',
        'Submitted'   => 'primary',
        'In Review'   => 'warning',
        'Forwarded'   => 'info',
        'Endorsed'    => 'info',
        'For Approval'=> 'warning',
        'Approved'    => 'success',
        'Returned'    => 'danger',
        'Archived'    => 'secondary',
    ];

    public const CATEGORY_ICONS = [
        'Memorandum'        => 'bi-envelope',
        'Request'           => 'bi-file-earmark-text',
        'Endorsement'       => 'bi-hand-thumbs-up',
        'Clearance'         => 'bi-shield-check',
        'Budget Proposal'   => 'bi-cash-stack',
        'Leave Request'     => 'bi-calendar-x',
        'Special Order'     => 'bi-file-earmark-ruled',
        'Travel Order'      => 'bi-airplane',
        'MANCOM Resolution' => 'bi-people',
        'BOT Resolution'    => 'bi-building',
        'Others'            => 'bi-file-earmark',
    ];

    public function examinations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(RecordsExamination::class)->latest('examined_at');
    }

    public function latestExamination(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(RecordsExamination::class)->latestOfMany('examined_at');
    }

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function workflowTemplate(): BelongsTo
    {
        return $this->belongsTo(WorkflowTemplate::class);
    }

    public function routes(): HasMany
    {
        return $this->hasMany(DocumentRoute::class)->orderBy('step_order');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(DocumentLog::class)->latest();
    }

    public function relatedDocuments(): BelongsToMany
    {
        return $this->belongsToMany(
            Document::class, 'document_relations',
            'document_id', 'related_document_id'
        );
    }

    public function statusColor(): string
    {
        return self::STATUS_COLORS[$this->status] ?? 'secondary';
    }

    public function categoryIcon(): string
    {
        return self::CATEGORY_ICONS[$this->category] ?? 'bi-file-earmark';
    }

    public function isOverdue(): bool
    {
        return $this->due_date
            && $this->due_date->isPast()
            && !in_array($this->status, ['Approved', 'Archived']);
    }

    public function currentRoute(): ?DocumentRoute
    {
        return $this->routes()->where('action', 'Pending')->orderBy('step_order')->first();
    }

    public static function generateDocNumber(string $campusCode, string $category): string
    {
        $prefix = match($category) {
            'Memorandum'        => 'MEMO',
            'Request'           => 'REQ',
            'Endorsement'       => 'END',
            'Clearance'         => 'CLR',
            'Budget Proposal'   => 'BUD',
            'Leave Request'     => 'LVE',
            'Special Order'     => 'SO',
            'Travel Order'      => 'TO',
            'MANCOM Resolution' => 'MANCOM',
            'BOT Resolution'    => 'BOT',
            default             => 'DOC',
        };

        $year  = date('y');
        $count = self::whereYear('created_at', date('Y'))
                     ->where('category', $category)
                     ->count() + 1;

        return sprintf('%s-%s-%s-%05d', $campusCode, $year, $prefix, $count);
    }

    public static function generateBarcode(string $docNumber): string
    {
        return strtoupper(str_replace([' ', '-'], '', $docNumber));
    }
}
