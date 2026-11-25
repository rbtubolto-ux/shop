<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// FAM 13.1: retention code before filing (3.5), confidential records kept apart (3.9),
// disposition from the semi-annual classification (3.13, 3.15).
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->string('retention_code', 100)->nullable()->after('container');
            $table->boolean('is_confidential')->default(false)->after('retention_code');
            $table->enum('disposition', ['For Retention', 'Archive', 'Dispose'])->nullable()->after('is_confidential');
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn(['retention_code', 'is_confidential', 'disposition']);
        });
    }
};
