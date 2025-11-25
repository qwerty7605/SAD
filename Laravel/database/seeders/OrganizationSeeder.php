<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrganizationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('organizations')->insert([
            [
                'org_code' => 'VPSD',
                'org_name' => 'Vice President for Student Development',
                'org_type' => 'student_services',
                'department' => 'Student Affairs',
                'is_active' => true,
                'requires_clearance' => true,
            ],
            [
                'org_code' => 'LIB',
                'org_name' => 'College Chief Librarian',
                'org_type' => 'academic',
                'department' => 'Library',
                'is_active' => true,
                'requires_clearance' => true,
            ],
            [
                'org_code' => 'DIGITS',
                'org_name' => 'DIGITS - IT Department Student Organization',
                'org_type' => 'academic',
                'department' => 'Information Technology',
                'is_active' => true,
                'requires_clearance' => true,
            ],
        ]);
    }
}
