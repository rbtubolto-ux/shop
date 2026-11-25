<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Routing slip (form PSHS-00-F-RMU-01): a subject per routing step, the subject carried to each
// route, and "Other instructions" typed on the document.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workflow_steps', function (Blueprint $table) {
            $table->string('subject', 40)->nullable()->after('role_required');
        });
        Schema::table('document_routes', function (Blueprint $table) {
            $table->string('subject', 40)->nullable()->after('step_name');
        });
        Schema::table('documents', function (Blueprint $table) {
            $table->text('routing_instructions')->nullable()->after('remarks');
        });
    }

    public function down(): void
    {
        Schema::table('documents', fn (Blueprint $t) => $t->dropColumn('routing_instructions'));
        Schema::table('document_routes', fn (Blueprint $t) => $t->dropColumn('subject'));
        Schema::table('workflow_steps', fn (Blueprint $t) => $t->dropColumn('subject'));
    }
};
