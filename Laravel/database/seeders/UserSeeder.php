<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // System Admin
        DB::table('users')->insert([
            'user_id' => 1,
            'username' => 'admin',
            'password_hash' => Hash::make('password'),
            'email' => 'admin@lnu.edu.ph',
            'user_type' => 'sys_admin',
            'is_active' => true,
        ]);

        // Organization Admins
        DB::table('users')->insert([
            [
                'user_id' => 2,
                'username' => 'vpsd_admin',
                'password_hash' => Hash::make('password'),
                'email' => 'vpsd@lnu.edu.ph',
                'user_type' => 'org_admin',
                'is_active' => true,
            ],
            [
                'user_id' => 3,
                'username' => 'librarian',
                'password_hash' => Hash::make('password'),
                'email' => 'librarian@lnu.edu.ph',
                'user_type' => 'org_admin',
                'is_active' => true,
            ],
            [
                'user_id' => 4,
                'username' => 'adviser',
                'password_hash' => Hash::make('password'),
                'email' => 'adviser@lnu.edu.ph',
                'user_type' => 'org_admin',
                'is_active' => true,
            ],
            [
                'user_id' => 5,
                'username' => 'treasurer',
                'password_hash' => Hash::make('password'),
                'email' => 'treasurer@lnu.edu.ph',
                'user_type' => 'org_admin',
                'is_active' => true,
            ],
        ]);

        // Students
        DB::table('users')->insert([
            [
                'user_id' => 6,
                'username' => '2020-12345',
                'password_hash' => Hash::make('password'),
                'email' => 'juan.delacruz@lnu.edu.ph',
                'user_type' => 'student',
                'is_active' => true,
            ],
            [
                'user_id' => 7,
                'username' => '2021-67890',
                'password_hash' => Hash::make('password'),
                'email' => 'maria.santos@lnu.edu.ph',
                'user_type' => 'student',
                'is_active' => true,
            ],
            [
                'user_id' => 8,
                'username' => '2022-11111',
                'password_hash' => Hash::make('password'),
                'email' => 'pedro.reyes@lnu.edu.ph',
                'user_type' => 'student',
                'is_active' => true,
            ],
        ]);
    }
}
