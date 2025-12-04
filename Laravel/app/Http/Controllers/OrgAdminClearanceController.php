<?php

namespace App\Http\Controllers;

use App\Models\ClearanceItem;
use App\Models\OrganizationAdmin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrgAdminClearanceController extends Controller
{
    /**
     * Get all clearance items assigned to the authenticated org admin (signatory)
     */
    public function index(Request $request)
    {
        // Get the authenticated user's org admin record
        $orgAdmin = OrganizationAdmin::where('admin_id', $request->user()->user_id)->first();

        if (!$orgAdmin) {
            return response()->json(['message' => 'Organization admin not found'], 404);
        }

        // Check if admin is active
        if (!$orgAdmin->is_active) {
            return response()->json(['message' => 'Your admin account has been deactivated'], 403);
        }

        // Get clearance items assigned specifically to this signatory
        $clearanceItems = ClearanceItem::where('required_signatory_id', $orgAdmin->admin_id)
            ->with([
                'clearance.student.user',
                'clearance.term',
                'organization',
                'requiredSignatory'
            ])
            ->get()
            ->map(function ($item) {
                return [
                    'item_id' => $item->item_id,
                    'clearance_id' => $item->clearance_id,
                    'org_id' => $item->org_id,
                    'status' => $item->status,
                    'approved_by' => $item->approved_by,
                    'approved_date' => $item->approved_date,
                    'is_auto_approved' => $item->is_auto_approved,
                    'created_at' => $item->created_at,
                    'status_updated' => $item->status_updated,
                    // Student details
                    'student_number' => $item->clearance->student->student_number ?? null,
                    'student_name' => $item->clearance->student
                        ? trim(($item->clearance->student->first_name ?? '') . ' ' .
                               ($item->clearance->student->middle_name ?? '') . ' ' .
                               ($item->clearance->student->last_name ?? ''))
                        : null,
                    'year_level' => $item->clearance->student->year_level ?? null,
                    'section' => $item->clearance->student->section ?? null,
                    'course' => $item->clearance->student->course ?? null,
                    // Term details
                    'term_name' => $item->clearance->term->term_name ?? null,
                    'academic_year' => $item->clearance->term->academic_year ?? null,
                ];
            });

        return response()->json($clearanceItems);
    }

    /**
     * Approve a clearance item (only items assigned to this signatory)
     */
    public function approve(Request $request, $itemId)
    {
        $orgAdmin = OrganizationAdmin::where('admin_id', $request->user()->user_id)->first();

        if (!$orgAdmin) {
            return response()->json(['message' => 'Organization admin not found'], 404);
        }

        // Check if admin is active
        if (!$orgAdmin->is_active) {
            return response()->json(['message' => 'Your admin account has been deactivated'], 403);
        }

        // Verify this item is assigned to this signatory and check if clearance is locked
        $clearanceItem = ClearanceItem::where('item_id', $itemId)
            ->where('required_signatory_id', $orgAdmin->admin_id)
            ->with('clearance')
            ->first();

        if (!$clearanceItem) {
            return response()->json(['message' => 'Clearance item not found or not assigned to you'], 404);
        }

        // Check if clearance is locked
        if ($clearanceItem->clearance->is_locked) {
            return response()->json(['message' => 'This clearance has been locked and cannot be modified'], 403);
        }

        // Check if already approved
        if ($clearanceItem->status === 'approved') {
            return response()->json(['message' => 'This clearance item has already been approved'], 400);
        }

        $clearanceItem->update([
            'status' => 'approved',
            'approved_by' => $orgAdmin->admin_id,
            'approved_date' => now(),
        ]);

        // Check if all items are approved to update overall status
        $allItemsApproved = $clearanceItem->clearance->items()
            ->where('status', '!=', 'approved')
            ->count() === 0;

        if ($allItemsApproved) {
            $clearanceItem->clearance->update([
                'overall_status' => 'approved',
                'approved_date' => now(),
            ]);
        }

        return response()->json([
            'message' => 'Clearance approved successfully',
            'clearance_item' => $clearanceItem,
            'overall_status_updated' => $allItemsApproved
        ]);
    }

    /**
     * Mark clearance item as needs compliance (only items assigned to this signatory)
     */
    public function needsCompliance(Request $request, $itemId)
    {
        $orgAdmin = OrganizationAdmin::where('admin_id', $request->user()->user_id)->first();

        if (!$orgAdmin) {
            return response()->json(['message' => 'Organization admin not found'], 404);
        }

        // Check if admin is active
        if (!$orgAdmin->is_active) {
            return response()->json(['message' => 'Your admin account has been deactivated'], 403);
        }

        // Verify this item is assigned to this signatory
        $clearanceItem = ClearanceItem::where('item_id', $itemId)
            ->where('required_signatory_id', $orgAdmin->admin_id)
            ->with('clearance')
            ->first();

        if (!$clearanceItem) {
            return response()->json(['message' => 'Clearance item not found or not assigned to you'], 404);
        }

        // Check if clearance is locked
        if ($clearanceItem->clearance->is_locked) {
            return response()->json(['message' => 'This clearance has been locked and cannot be modified'], 403);
        }

        $clearanceItem->update([
            'status' => 'needs_compliance',
            'approved_by' => $orgAdmin->admin_id,  // Track who marked it as needs compliance
            'approved_date' => now(),  // Track when this action was taken
        ]);

        // Update overall status to incomplete
        $clearanceItem->clearance->update([
            'overall_status' => 'incomplete',
        ]);

        return response()->json([
            'message' => 'Clearance marked as needs compliance',
            'clearance_item' => $clearanceItem
        ]);
    }

    /**
     * Bulk approve clearance items (only items assigned to this signatory)
     */
    public function bulkApprove(Request $request)
    {
        $request->validate([
            'item_ids' => 'required|array',
            'item_ids.*' => 'integer|exists:clearance_items,item_id',
        ]);

        $orgAdmin = OrganizationAdmin::where('admin_id', $request->user()->user_id)->first();

        if (!$orgAdmin) {
            return response()->json(['message' => 'Organization admin not found'], 404);
        }

        // Check if admin is active
        if (!$orgAdmin->is_active) {
            return response()->json(['message' => 'Your admin account has been deactivated'], 403);
        }

        // Use transaction for bulk operations
        DB::beginTransaction();
        try {
            // Only approve items assigned to this signatory, not locked, and not already approved
            $updated = ClearanceItem::whereIn('item_id', $request->item_ids)
                ->where('required_signatory_id', $orgAdmin->admin_id)
                ->where('status', '!=', 'approved')
                ->whereHas('clearance', function($q) {
                    $q->where('is_locked', false);
                })
                ->update([
                    'status' => 'approved',
                    'approved_by' => $orgAdmin->admin_id,
                    'approved_date' => now(),
                ]);

            // Update overall status for affected clearances
            $affectedClearanceIds = ClearanceItem::whereIn('item_id', $request->item_ids)
                ->pluck('clearance_id')
                ->unique();

            foreach ($affectedClearanceIds as $clearanceId) {
                $clearance = \App\Models\StudentClearance::find($clearanceId);
                $allItemsApproved = $clearance->items()->where('status', '!=', 'approved')->count() === 0;

                if ($allItemsApproved) {
                    $clearance->update([
                        'overall_status' => 'approved',
                        'approved_date' => now(),
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'message' => "Successfully approved {$updated} clearance(s)",
                'updated_count' => $updated
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Bulk approval failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get clearance statistics for items assigned to this signatory
     */
    public function statistics(Request $request)
    {
        $orgAdmin = OrganizationAdmin::where('admin_id', $request->user()->user_id)->first();

        if (!$orgAdmin) {
            return response()->json(['message' => 'Organization admin not found'], 404);
        }

        // Get statistics only for items assigned to this signatory
        $stats = ClearanceItem::where('required_signatory_id', $orgAdmin->admin_id)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status');

        return response()->json([
            'total' => $stats->sum(),
            'approved' => $stats['approved'] ?? 0,
            'pending' => $stats['pending'] ?? 0,
            'needs_compliance' => $stats['needs_compliance'] ?? 0,
        ]);
    }
}
