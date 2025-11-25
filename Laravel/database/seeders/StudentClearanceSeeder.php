<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StudentClearanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clearances = [];

        // Create clearances for all IT students (user_id 6 to 141)
        // Term ID 1 = First Semester 2024-2025 (current term)
        for ($studentId = 6; $studentId <= 141; $studentId++) {
            $clearances[] = [
                'student_id' => $studentId,
                'term_id' => 1,
                'overall_status' => 'pending',
            ];
        }

        DB::table('student_clearances')->insert($clearances);
    }
}
