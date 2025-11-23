<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SystemAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('system_admins')->insert([
            'sys_admin_id' => 1,
            'admin_level' => 'super_admin',
            'full_name' => 'System Administrator',
            'department' => 'MIS Department',
        ]);
    }
}
