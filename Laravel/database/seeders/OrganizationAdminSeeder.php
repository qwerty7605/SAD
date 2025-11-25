<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrganizationAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('organization_admins')->insert([
            [
                'admin_id' => 2,
                'org_id' => 1, // VPSD
                'position' => 'Vice President for Student Development',
                'full_name' => 'Dr. Roberto Garcia',
                'is_active' => true,
            ],
            [
                'admin_id' => 3,
                'org_id' => 2, // LIB
                'position' => 'College Chief Librarian',
                'full_name' => 'Ms. Ana Mercado',
                'is_active' => true,
            ],
            [
                'admin_id' => 4,
                'org_id' => 3, // DIGITS - Adviser
                'position' => 'Academic Organization Adviser',
                'full_name' => 'Prof. Carlos Fernandez',
                'is_active' => true,
            ],
            [
                'admin_id' => 5,
                'org_id' => 3, // DIGITS - Treasurer
                'position' => 'Academic Organization Treasurer',
                'full_name' => 'Mrs. Linda Ramos',
                'is_active' => true,
            ],
        ]);
    }
}
