<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CampusSeeder extends Seeder
{
    public function run(): void
    {
        $campuses = [
            ['name' => 'Main Campus',   'code' => 'MAIN',     'address' => 'Main Campus Address'],
            ['name' => 'Campus A',      'code' => 'CAMPUS-A', 'address' => 'Campus A Address'],
            ['name' => 'Campus B',      'code' => 'CAMPUS-B', 'address' => 'Campus B Address'],
        ];

        foreach ($campuses as $campus) {
            DB::table('campuses')->insertOrIgnore(array_merge($campus, [
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
