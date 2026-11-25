<?php

namespace Database\Seeders;

use App\Models\Document;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

// Default routing for each document category. The Records Management Officer (Document Controller)
// reviews every document before the Campus Director acts (FAM 13.1 section 4.0, step 4).
// Categories that already have a template are skipped, so existing workflows are not changed.
// Safe to run more than once: php artisan db:seed --class=DefaultWorkflowSeeder
class DefaultWorkflowSeeder extends Seeder
{
    public function run(): void
    {
        $campusId = DB::table('campuses')->where('code', 'MAIN')->value('id');
        if (!$campusId) return;

        $ids = DB::table('users')
            ->whereIn('email', ['controller@main.edu', 'director@main.edu', 'budget@main.edu', 'hr@main.edu'])
            ->pluck('id', 'email');

        $rmo = ['step_name' => 'Document Controller Review', 'assigned_to' => $ids['controller@main.edu'] ?? null,
                'role_required' => 'document_controller', 'pending_threshold_hours' => 48];
        $director = ['step_name' => 'Campus Director Approval', 'assigned_to' => $ids['director@main.edu'] ?? null,
                'role_required' => 'campus_director', 'pending_threshold_hours' => 24];
        $budget = ['step_name' => 'Budget Officer Review', 'assigned_to' => $ids['budget@main.edu'] ?? null,
                'role_required' => 'routing_unit', 'pending_threshold_hours' => 48];
        $hr = ['step_name' => 'HR Officer Review', 'assigned_to' => $ids['hr@main.edu'] ?? null,
                'role_required' => 'routing_unit', 'pending_threshold_hours' => 48];

        $defaults = [
            'Request'           => [$rmo, $director],
            'Endorsement'       => [$rmo, $director],
            'Clearance'         => [$hr, $rmo, $director],
            'Special Order'     => [$rmo, $director],
            'Travel Order'      => [$budget, $rmo, $director],
            'MANCOM Resolution' => [$rmo, $director],
            'BOT Resolution'    => [$rmo, $director],
        ];

        foreach ($defaults as $category => $steps) {
            if (!in_array($category, Document::CATEGORIES, true)) continue;

            $exists = DB::table('workflow_templates')
                ->where('campus_id', $campusId)
                ->where('document_type', $category)
                ->exists();
            if ($exists) continue;

            $templateId = DB::table('workflow_templates')->insertGetId([
                'campus_id'     => $campusId,
                'name'          => $category . ' Workflow',
                'document_type' => $category,
                'description'   => 'Default routing for ' . strtolower($category) . ' documents',
                'is_active'     => true,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);

            foreach ($steps as $i => $step) {
                DB::table('workflow_steps')->insert(array_merge($step, [
                    'workflow_template_id' => $templateId,
                    'step_order'           => $i + 1,
                    'created_at'           => now(),
                    'updated_at'           => now(),
                ]));
            }
        }
    }
}
