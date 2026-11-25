<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campus_id')->constrained()->cascadeOnDelete();
            $table->string('doc_number')->unique();
            $table->string('barcode')->nullable()->unique();
            $table->string('title');
            $table->enum('category', [
                'Memorandum', 'Request', 'Endorsement', 'Clearance',
                'Budget Proposal', 'Leave Request', 'Special Order',
                'Travel Order', 'MANCOM Resolution', 'BOT Resolution', 'Others'
            ]);
            $table->enum('direction', ['Incoming', 'Outgoing'])->default('Incoming');
            $table->text('description')->nullable();
            $table->enum('status', [
                'Draft', 'Submitted', 'In Review', 'Forwarded',
                'Endorsed', 'For Approval', 'Approved', 'Returned', 'Archived'
            ])->default('Draft');
            $table->string('originating_office')->nullable();
            $table->string('signatory')->nullable();
            $table->date('date_submitted')->nullable();
            $table->date('due_date')->nullable();
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->foreignId('submitted_by')->constrained('users');
            $table->foreignId('workflow_template_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('current_step')->default(0);
            $table->string('cabinet')->nullable();
            $table->string('drawer')->nullable();
            $table->string('container')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
