<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AcademicTermSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('academic_terms')->insert([
            [
                'academic_year' => '2024-2025',
                'semester' => 'first',
                'term_name' => 'First Semester 2024-2025',
                'start_date' => '2024-08-01',
                'end_date' => '2024-12-15',
                'enrollment_start' => '2024-07-01',
                'enrollment_end' => '2024-08-15',
                'is_current' => true,
                'clearance_deadline' => '2024-07-25',
            ],
            [
                'academic_year' => '2024-2025',
                'semester' => 'second',
                'term_name' => 'Second Semester 2024-2025',
                'start_date' => '2025-01-06',
                'end_date' => '2025-05-31',
                'enrollment_start' => '2024-12-01',
                'enrollment_end' => '2025-01-15',
                'is_current' => false,
                'clearance_deadline' => '2024-12-20',
            ],
        ]);
    }
}
