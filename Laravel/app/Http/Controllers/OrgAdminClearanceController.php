<?php

namespace App\Http\Controllers;

use App\Models\ClearanceItem;
use App\Models\OrganizationAdmin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrgAdminClearanceController extends Controller
{
    /**
     * Get all clearance items for the authenticated org admin's organization
     */
    public function index(Request $request)
    {
        // Get the authenticated user's org admin record
        $orgAdmin = OrganizationAdmin::where('admin_id', $request->user()->user_id)->first();

        if (!$orgAdmin) {
            return response()->json(['message' => 'Organization admin not found'], 404);
        }

        // Get clearance items for this organization with student details
        $clearanceItems = ClearanceItem::where('org_id', $orgAdmin->org_id)
            ->with([
                'clearance.student.user',
                'clearance.term',
                'organization'
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
     * Approve a clearance item
     */
    public function approve(Request $request, $itemId)
    {
        $orgAdmin = OrganizationAdmin::where('admin_id', $request->user()->user_id)->first();

        if (!$orgAdmin) {
            return response()->json(['message' => 'Organization admin not found'], 404);
        }

        $clearanceItem = ClearanceItem::where('item_id', $itemId)
            ->where('org_id', $orgAdmin->org_id)
            ->first();

        if (!$clearanceItem) {
            return response()->json(['message' => 'Clearance item not found'], 404);
        }

        $clearanceItem->update([
            'status' => 'approved',
            'approved_by' => $orgAdmin->admin_id,
            'approved_date' => now(),
        ]);

        return response()->json([
            'message' => 'Clearance approved successfully',
            'clearance_item' => $clearanceItem
        ]);
    }

    /**
     * Mark clearance item as needs compliance
     */
    public function needsCompliance(Request $request, $itemId)
    {
        $orgAdmin = OrganizationAdmin::where('admin_id', $request->user()->user_id)->first();

        if (!$orgAdmin) {
            return response()->json(['message' => 'Organization admin not found'], 404);
        }

        $clearanceItem = ClearanceItem::where('item_id', $itemId)
            ->where('org_id', $orgAdmin->org_id)
            ->first();

        if (!$clearanceItem) {
            return response()->json(['message' => 'Clearance item not found'], 404);
        }

        $clearanceItem->update([
            'status' => 'needs_compliance',
            'approved_by' => $orgAdmin->admin_id,
            'approved_date' => null,
        ]);

        return response()->json([
            'message' => 'Clearance marked as needs compliance',
            'clearance_item' => $clearanceItem
        ]);
    }

    /**
     * Bulk approve clearance items
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

        $updated = ClearanceItem::whereIn('item_id', $request->item_ids)
            ->where('org_id', $orgAdmin->org_id)
            ->update([
                'status' => 'approved',
                'approved_by' => $orgAdmin->admin_id,
                'approved_date' => now(),
            ]);

        return response()->json([
            'message' => "Successfully approved {$updated} clearance(s)",
            'updated_count' => $updated
        ]);
    }

    /**
     * Get clearance statistics for the org admin's organization
     */
    public function statistics(Request $request)
    {
        $orgAdmin = OrganizationAdmin::where('admin_id', $request->user()->user_id)->first();

        if (!$orgAdmin) {
            return response()->json(['message' => 'Organization admin not found'], 404);
        }

        $stats = ClearanceItem::where('org_id', $orgAdmin->org_id)
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
