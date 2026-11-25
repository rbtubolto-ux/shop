<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// FAM 13.1 §3.10: requisition procedures (forms 5.1.1 Records Requisition Slip, 5.1.2 Acknowledgement Slip).
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('records_requisitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained()->cascadeOnDelete();
            $table->foreignId('requested_by')->constrained('users');
            $table->text('purpose');
            $table->enum('status', ['Requested', 'Issued', 'Acknowledged', 'Returned'])->default('Requested');
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamp('returned_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('records_requisitions');
    }
};
