<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ClearanceItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Now creates separate clearance items for each signatory in an organization
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

            // VPSD clearance item (admin_id = 2)
            if ($distribution < 4) {
                // Fully approved
                $clearanceItems[] = [
                    'clearance_id' => $clearanceId,
                    'org_id' => 1,
                    'required_signatory_id' => 2,
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
                    'required_signatory_id' => 2,
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
                    'required_signatory_id' => 2,
                    'status' => 'pending',
                    'approved_by' => null,
                    'approved_date' => null,
                    'is_auto_approved' => false,
                ];
            }

            // Library clearance item (admin_id = 3)
            if ($distribution < 5) {
                // Fully approved
                $clearanceItems[] = [
                    'clearance_id' => $clearanceId,
                    'org_id' => 2,
                    'required_signatory_id' => 3,
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
                    'required_signatory_id' => 3,
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
                    'required_signatory_id' => 3,
                    'status' => 'pending',
                    'approved_by' => null,
                    'approved_date' => null,
                    'is_auto_approved' => false,
                ];
            }

            // DIGITS clearance items - NOW CREATES TWO ITEMS (one for Adviser, one for Treasurer)
            // DIGITS Adviser item (admin_id = 4)
            if ($distribution < 6) {
                // Fully approved
                $clearanceItems[] = [
                    'clearance_id' => $clearanceId,
                    'org_id' => 3,
                    'required_signatory_id' => 4, // Adviser
                    'status' => 'approved',
                    'approved_by' => 4,
                    'approved_date' => Carbon::now()->subDays(rand(1, 15)),
                    'is_auto_approved' => false,
                ];
            } elseif ($distribution < 8) {
                // Needs compliance
                $clearanceItems[] = [
                    'clearance_id' => $clearanceId,
                    'org_id' => 3,
                    'required_signatory_id' => 4, // Adviser
                    'status' => 'needs_compliance',
                    'approved_by' => 4,
                    'approved_date' => null,
                    'is_auto_approved' => false,
                ];
            } else {
                // Pending
                $clearanceItems[] = [
                    'clearance_id' => $clearanceId,
                    'org_id' => 3,
                    'required_signatory_id' => 4, // Adviser
                    'status' => 'pending',
                    'approved_by' => null,
                    'approved_date' => null,
                    'is_auto_approved' => false,
                ];
            }

            // DIGITS Treasurer item (admin_id = 5) - NEW!
            $treasurerDistribution = ($clearanceId + 3) % 10; // Different distribution for variety
            if ($treasurerDistribution < 5) {
                // Fully approved
                $clearanceItems[] = [
                    'clearance_id' => $clearanceId,
                    'org_id' => 3,
                    'required_signatory_id' => 5, // Treasurer
                    'status' => 'approved',
                    'approved_by' => 5,
                    'approved_date' => Carbon::now()->subDays(rand(1, 15)),
                    'is_auto_approved' => false,
                ];
            } elseif ($treasurerDistribution < 7) {
                // Needs compliance
                $clearanceItems[] = [
                    'clearance_id' => $clearanceId,
                    'org_id' => 3,
                    'required_signatory_id' => 5, // Treasurer
                    'status' => 'needs_compliance',
                    'approved_by' => 5,
                    'approved_date' => null,
                    'is_auto_approved' => false,
                ];
            } else {
                // Pending
                $clearanceItems[] = [
                    'clearance_id' => $clearanceId,
                    'org_id' => 3,
                    'required_signatory_id' => 5, // Treasurer
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
