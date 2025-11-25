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
        $students = [];
        $studentId = 6;

        // First names pool
        $firstNames = ['Prince', 'Eujan', 'Wayne', 'Julius Rey', 'Jose', 'Pineapple', 'Princess', 'Saging', 'Kato', 'Elena',
                       'Carlos', 'Sofia', 'Rafael', 'Isabel', 'Diego', 'Lucia', 'Antonio', 'Gabriela', 'Fernando', 'May'];

        // Last names pool
        $lastNames = ['Yan', 'Zeta', 'Tabalanza', 'dela Pena', 'Rizal', 'Pogi', 'Tan', 'Rambutan',
                      'Orange', 'Perez', 'Sanchez', 'Ramirez', 'Sam Ting', 'Flores', 'Rivera', 'Gomez'];

        // Year 1 - Sections AI-11 to AI-15 (5 sections, 8 students each = 40 students)
        for ($section = 11; $section <= 15; $section++) {
            for ($i = 0; $i < 8; $i++) {
                $students[] = [
                    'student_id' => $studentId,
                    'student_number' => '2024-' . str_pad($studentId, 5, '0', STR_PAD_LEFT),
                    'first_name' => $firstNames[array_rand($firstNames)],
                    'middle_name' => $lastNames[array_rand($lastNames)],
                    'last_name' => $lastNames[array_rand($lastNames)],
                    'course' => 'Bachelor of Science in Information Technology',
                    'year_level' => 1,
                    'section' => 'AI-' . $section,
                    'contact_number' => '0919' . str_pad(rand(1000000, 9999999), 7, '0', STR_PAD_LEFT),
                    'date_enrolled' => '2024-07-15',
                    'enrollment_status' => 'enrolled',
                    'student_type' => ($i % 3 == 0) ?'regular' : 'irregular',
                ];
                $studentId++;
            }
        }

        // Year 2 - Sections AI-21 to AI-25 (5 sections, 8 students each = 40 students)
        for ($section = 21; $section <= 25; $section++) {
            for ($i = 0; $i < 8; $i++) {
                $students[] = [
                    'student_id' => $studentId,
                    'student_number' => '2023-' . str_pad($studentId - 40, 5, '0', STR_PAD_LEFT),
                    'first_name' => $firstNames[array_rand($firstNames)],
                    'middle_name' => $lastNames[array_rand($lastNames)],
                    'last_name' => $lastNames[array_rand($lastNames)],
                    'course' => 'Bachelor of Science in Information Technology',
                    'year_level' => 2,
                    'section' => 'AI-' . $section,
                    'contact_number' => '0919' . str_pad(rand(1000000, 9999999), 7, '0', STR_PAD_LEFT),
                    'date_enrolled' => '2023-07-15',
                    'enrollment_status' => 'enrolled',
                    'student_type' => ($i % 3 == 0) ?'regular' : 'irregular',
                ];
                $studentId++;
            }
        }

        // Year 3 - Sections AI-31 to AI-34 (4 sections, 8 students each = 32 students)
        for ($section = 31; $section <= 34; $section++) {
            for ($i = 0; $i < 8; $i++) {
                $students[] = [
                    'student_id' => $studentId,
                    'student_number' => '2022-' . str_pad($studentId - 80, 5, '0', STR_PAD_LEFT),
                    'first_name' => $firstNames[array_rand($firstNames)],
                    'middle_name' => $lastNames[array_rand($lastNames)],
                    'last_name' => $lastNames[array_rand($lastNames)],
                    'course' => 'Bachelor of Science in Information Technology',
                    'year_level' => 3,
                    'section' => 'AI-' . $section,
                    'contact_number' => '0919' . str_pad(rand(1000000, 9999999), 7, '0', STR_PAD_LEFT),
                    'date_enrolled' => '2022-07-15',
                    'enrollment_status' => 'enrolled',
                    'student_type' => ($i % 3 == 0) ?'regular' : 'irregular',
                ];
                $studentId++;
            }
        }

        // Year 4 - Sections AI-41 to AI-43 (3 sections, 8 students each = 24 students)
        for ($section = 41; $section <= 43; $section++) {
            for ($i = 0; $i < 8; $i++) {
                $students[] = [
                    'student_id' => $studentId,
                    'student_number' => '2021-' . str_pad($studentId - 112, 5, '0', STR_PAD_LEFT),
                    'first_name' => $firstNames[array_rand($firstNames)],
                    'middle_name' => $lastNames[array_rand($lastNames)],
                    'last_name' => $lastNames[array_rand($lastNames)],
                    'course' => 'Bachelor of Science in Information Technology',
                    'year_level' => 4,
                    'section' => 'AI-' . $section,
                    'contact_number' => '0919' . str_pad(rand(1000000, 9999999), 7, '0', STR_PAD_LEFT),
                    'date_enrolled' => '2021-07-15',
                    'enrollment_status' => 'enrolled',
                    'student_type' => ($i % 5 == 0) ?'irregular' : 'regular',
                ];
                $studentId++;
            }
        }

        DB::table('students')->insert($students);
    }
}
