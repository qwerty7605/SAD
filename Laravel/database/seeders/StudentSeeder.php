<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('students')->insert([
            [
                'student_id' => 6,
                'student_number' => '2020-12345',
                'first_name' => 'Juan',
                'middle_name' => 'Pedro',
                'last_name' => 'Dela Cruz',
                'course' => 'Bachelor of Science in Information Technology',
                'year_level' => 4,
                'section' => 'A',
                'contact_number' => '09123456789',
                'date_enrolled' => '2024-07-15',
                'enrollment_status' => 'enrolled',
            ],
            [
                'student_id' => 7,
                'student_number' => '2021-67890',
                'first_name' => 'Maria',
                'middle_name' => 'Clara',
                'last_name' => 'Santos',
                'course' => 'Bachelor of Elementary Education',
                'year_level' => 3,
                'section' => 'B',
                'contact_number' => '09187654321',
                'date_enrolled' => '2024-07-16',
                'enrollment_status' => 'enrolled',
            ],
            [
                'student_id' => 8,
                'student_number' => '2022-11111',
                'first_name' => 'Pedro',
                'middle_name' => 'Luis',
                'last_name' => 'Reyes',
                'course' => 'Bachelor of Science in Computer Science',
                'year_level' => 2,
                'section' => 'A',
                'contact_number' => '09991234567',
                'date_enrolled' => '2024-07-17',
                'enrollment_status' => 'enrolled',
            ],
        ]);
    }
}
