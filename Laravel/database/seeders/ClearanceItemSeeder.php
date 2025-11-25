<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ClearanceItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clearanceItems = [];

        // Organizations: 1=VPSD, 2=LIB, 3=DIGITS
        // Organization Admins: 2=VPSD, 3=LIB, 4=DIGITS Adviser, 5=DIGITS Treasurer

        // For each student clearance (clearance_id 1 to 136)
        for ($clearanceId = 1; $clearanceId <= 136; $clearanceId++) {
            // Determine status distribution
            // 40% fully approved, 30% needs compliance, 30% pending
            $distribution = $clearanceId % 10;

            // VPSD clearance item
            if ($distribution < 4) {
                // Fully approved
                $clearanceItems[] = [
                    'clearance_id' => $clearanceId,
                    'org_id' => 1,
                    'status' => 'approved',
                    'approved_by' => 2,
                    'approved_date' => Carbon::now()->subDays(rand(1, 15)),
                    'is_auto_approved' => false,
                ];
            } elseif ($distribution < 7) {
                // Needs compliance
                $clearanceItems[] = [
                    'clearance_id' => $clearanceId,
                    'org_id' => 1,
                    'status' => 'needs_compliance',
                    'approved_by' => 2,
                    'approved_date' => null,
                    'is_auto_approved' => false,
                ];
            } else {
                // Pending
                $clearanceItems[] = [
                    'clearance_id' => $clearanceId,
                    'org_id' => 1,
                    'status' => 'pending',
                    'approved_by' => null,
                    'approved_date' => null,
                    'is_auto_approved' => false,
                ];
            }

            // Library clearance item
            if ($distribution < 5) {
                // Fully approved
                $clearanceItems[] = [
                    'clearance_id' => $clearanceId,
                    'org_id' => 2,
                    'status' => 'approved',
                    'approved_by' => 3,
                    'approved_date' => Carbon::now()->subDays(rand(1, 15)),
                    'is_auto_approved' => false,
                ];
            } elseif ($distribution < 8) {
                // Needs compliance
                $clearanceItems[] = [
                    'clearance_id' => $clearanceId,
                    'org_id' => 2,
                    'status' => 'needs_compliance',
                    'approved_by' => 3,
                    'approved_date' => null,
                    'is_auto_approved' => false,
                ];
            } else {
                // Pending
                $clearanceItems[] = [
                    'clearance_id' => $clearanceId,
                    'org_id' => 2,
                    'status' => 'pending',
                    'approved_by' => null,
                    'approved_date' => null,
                    'is_auto_approved' => false,
                ];
            }

            // DIGITS clearance items
            if ($distribution < 6) {
                // Fully approved
                $clearanceItems[] = [
                    'clearance_id' => $clearanceId,
                    'org_id' => 3,
                    'status' => 'approved',
                    'approved_by' => 4, // Adviser
                    'approved_date' => Carbon::now()->subDays(rand(1, 15)),
                    'is_auto_approved' => false,
                ];
            } elseif ($distribution < 8) {
                // Needs compliance
                $clearanceItems[] = [
                    'clearance_id' => $clearanceId,
                    'org_id' => 3,
                    'status' => 'needs_compliance',
                    'approved_by' => 4, // Adviser
                    'approved_date' => null,
                    'is_auto_approved' => false,
                ];
            } else {
                // Pending
                $clearanceItems[] = [
                    'clearance_id' => $clearanceId,
                    'org_id' => 3,
                    'status' => 'pending',
                    'approved_by' => null,
                    'approved_date' => null,
                    'is_auto_approved' => false,
                ];
            }
        }

        // Insert all clearance items
        // Split into chunks to avoid memory issues
        $chunks = array_chunk($clearanceItems, 100);
        foreach ($chunks as $chunk) {
            DB::table('clearance_items')->insert($chunk);
        }
    }
}
