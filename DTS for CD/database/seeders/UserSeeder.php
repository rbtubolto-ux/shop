<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $main = DB::table('campuses')->where('code', 'MAIN')->value('id');
        $campusA = DB::table('campuses')->where('code', 'CAMPUS-A')->value('id');

        $users = [
            [
                'campus_id'  => $main,
                'name'       => 'Document Controller',
                'email'      => 'controller@main.edu',
                'password'   => Hash::make('password'),
                'role'       => 'document_controller',
                'department' => 'Office of the Campus Director',
                'position'   => 'Administrative Staff',
            ],
            [
                'campus_id'  => $main,
                'name'       => 'Dr. Maria Santos',
                'email'      => 'director@main.edu',
                'password'   => Hash::make('password'),
                'role'       => 'campus_director',
                'department' => 'Office of the Campus Director',
                'position'   => 'Campus Director',
            ],
            [
                'campus_id'  => $main,
                'name'       => 'Budget Officer',
                'email'      => 'budget@main.edu',
                'password'   => Hash::make('password'),
                'role'       => 'routing_unit',
                'department' => 'Finance Office',
                'position'   => 'Budget Officer',
            ],
            [
                'campus_id'  => $main,
                'name'       => 'HR Officer',
                'email'      => 'hr@main.edu',
                'password'   => Hash::make('password'),
                'role'       => 'routing_unit',
                'department' => 'Human Resources',
                'position'   => 'HR Officer',
            ],
            [
                'campus_id'  => $main,
                'name'       => 'Juan dela Cruz',
                'email'      => 'submitter@main.edu',
                'password'   => Hash::make('password'),
                'role'       => 'document_submitter',
                'department' => 'College of Engineering',
                'position'   => 'Faculty Member',
            ],
            [
                'campus_id'  => $campusA,
                'name'       => 'Campus A Controller',
                'email'      => 'controller@campus-a.edu',
                'password'   => Hash::make('password'),
                'role'       => 'document_controller',
                'department' => 'Office of the Campus Director',
                'position'   => 'Administrative Staff',
            ],
            [
                'campus_id'  => $campusA,
                'name'       => 'Dr. Pedro Reyes',
                'email'      => 'director@campus-a.edu',
                'password'   => Hash::make('password'),
                'role'       => 'campus_director',
                'department' => 'Office of the Campus Director',
                'position'   => 'Campus Director',
            ],
        ];

        foreach ($users as $user) {
            DB::table('users')->insertOrIgnore(array_merge($user, [
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
