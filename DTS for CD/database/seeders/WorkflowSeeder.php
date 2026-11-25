<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WorkflowSeeder extends Seeder
{
    public function run(): void
    {
        $mainId     = DB::table('campuses')->where('code', 'MAIN')->value('id');
        $directorId = DB::table('users')->where('email', 'director@main.edu')->value('id');
        $budgetId   = DB::table('users')->where('email', 'budget@main.edu')->value('id');
        $hrId       = DB::table('users')->where('email', 'hr@main.edu')->value('id');
        $controllerId = DB::table('users')->where('email', 'controller@main.edu')->value('id');

        $workflows = [
            [
                'name'          => 'Budget Request Workflow',
                'document_type' => 'Budget Proposal',
                'description'   => 'Routing for budget proposals and requests',
                'steps'         => [
                    ['step_order' => 1, 'step_name' => 'Budget Officer Review',    'assigned_to' => $budgetId,   'role_required' => 'routing_unit',    'pending_threshold_hours' => 48],
                    ['step_order' => 2, 'step_name' => 'Campus Director Approval', 'assigned_to' => $directorId, 'role_required' => 'campus_director',  'pending_threshold_hours' => 24],
                ],
            ],
            [
                'name'          => 'Leave Request Workflow',
                'document_type' => 'Leave Request',
                'description'   => 'Routing for faculty and staff leave requests',
                'steps'         => [
                    ['step_order' => 1, 'step_name' => 'HR Officer Review',        'assigned_to' => $hrId,       'role_required' => 'routing_unit',    'pending_threshold_hours' => 24],
                    ['step_order' => 2, 'step_name' => 'Campus Director Approval', 'assigned_to' => $directorId, 'role_required' => 'campus_director',  'pending_threshold_hours' => 24],
                ],
            ],
            [
                'name'          => 'General Document Workflow',
                'document_type' => 'Others',
                'description'   => 'Default routing for general documents',
                'steps'         => [
                    ['step_order' => 1, 'step_name' => 'Document Controller Review', 'assigned_to' => $controllerId, 'role_required' => 'document_controller', 'pending_threshold_hours' => 72],
                    ['step_order' => 2, 'step_name' => 'Campus Director Approval',   'assigned_to' => $directorId,   'role_required' => 'campus_director',     'pending_threshold_hours' => 24],
                ],
            ],
            [
                'name'          => 'Memorandum Workflow',
                'document_type' => 'Memorandum',
                'description'   => 'Routing for memoranda',
                'steps'         => [
                    ['step_order' => 1, 'step_name' => 'Document Controller Review', 'assigned_to' => $controllerId, 'role_required' => 'document_controller', 'pending_threshold_hours' => 48],
                    ['step_order' => 2, 'step_name' => 'Campus Director Approval',   'assigned_to' => $directorId,   'role_required' => 'campus_director',     'pending_threshold_hours' => 24],
                ],
            ],
        ];

        foreach ($workflows as $wf) {
            $steps = $wf['steps'];
            unset($wf['steps']);

            $templateId = DB::table('workflow_templates')->insertGetId(array_merge($wf, [
                'campus_id'  => $mainId,
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]));

            foreach ($steps as $step) {
                DB::table('workflow_steps')->insert(array_merge($step, [
                    'workflow_template_id' => $templateId,
                    'created_at'           => now(),
                    'updated_at'           => now(),
                ]));
            }
        }
    }
}
