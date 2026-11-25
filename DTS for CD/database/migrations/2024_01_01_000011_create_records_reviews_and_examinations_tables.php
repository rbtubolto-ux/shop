<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// FAM 13.1 section 3.14 (periodic file examination) and sections 3.12 / 3.13 (yearly inventory,
// semi-annual classification) with their recorded reviews.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('records_examinations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained()->cascadeOnDelete();
            $table->enum('condition', ['Good', 'Fair', 'Poor', 'Damaged']);
            $table->text('notes')->nullable();
            $table->foreignId('examined_by')->constrained('users');
            $table->timestamp('examined_at');
            $table->timestamps();
        });

        Schema::create('records_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campus_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['Yearly Inventory', 'Semi-annual Classification']);
            $table->foreignId('conducted_by')->constrained('users');
            $table->timestamp('conducted_at');
            $table->unsignedInteger('records_count')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('records_reviews');
        Schema::dropIfExists('records_examinations');
    }
};
