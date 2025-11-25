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

        // Students - Create user accounts for all IT students
        $studentUsers = [];

        // Total students: 136 (40 + 40 + 32 + 24)
        // Starting from user_id 6 to 141
        for ($userId = 6; $userId <= 141; $userId++) {
            $studentNumber = '';

            // Determine student number based on year level
            if ($userId <= 45) { // Year 1 (40 students)
                $studentNumber = '2024-' . str_pad($userId, 5, '0', STR_PAD_LEFT);
            } elseif ($userId <= 85) { // Year 2 (40 students)
                $studentNumber = '2023-' . str_pad($userId - 40, 5, '0', STR_PAD_LEFT);
            } elseif ($userId <= 117) { // Year 3 (32 students)
                $studentNumber = '2022-' . str_pad($userId - 80, 5, '0', STR_PAD_LEFT);
            } else { // Year 4 (24 students)
                $studentNumber = '2021-' . str_pad($userId - 112, 5, '0', STR_PAD_LEFT);
            }

            $studentUsers[] = [
                'user_id' => $userId,
                'username' => $studentNumber,
                'password_hash' => Hash::make('password'),
                'email' => strtolower($studentNumber) . '@lnu.edu.ph',
                'user_type' => 'student',
                'is_active' => true,
            ];
        }

        DB::table('users')->insert($studentUsers);
    }
}
