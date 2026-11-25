<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Columns the application uses but no migration created: signature images on documents and
// routing steps, and the approval fields on documents. Each column is added only if missing.
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('documents', 'signature_path')) {
            Schema::table('documents', fn (Blueprint $t) => $t->string('signature_path')->nullable());
        }
        if (!Schema::hasColumn('documents', 'approved_by')) {
            Schema::table('documents', fn (Blueprint $t) => $t->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete());
        }
        if (!Schema::hasColumn('documents', 'approved_at')) {
            Schema::table('documents', fn (Blueprint $t) => $t->timestamp('approved_at')->nullable());
        }
        if (!Schema::hasColumn('document_routes', 'signature_path')) {
            Schema::table('document_routes', fn (Blueprint $t) => $t->string('signature_path')->nullable());
        }
    }

    public function down(): void
    {
        Schema::table('document_routes', fn (Blueprint $t) => $t->dropColumn('signature_path'));
        Schema::table('documents', function (Blueprint $t) {
            $t->dropForeign(['approved_by']);
            $t->dropColumn(['signature_path', 'approved_by', 'approved_at']);
        });
    }
};
