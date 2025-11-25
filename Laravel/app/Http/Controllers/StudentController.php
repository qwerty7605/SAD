<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentClearance;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentController extends Controller
{
    /**
     * Get authenticated student's profile
     */
    public function getProfile(Request $request)
    {
        $user = $request->user();

        if ($user->user_type !== 'student') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $student = Student::where('student_id', $user->user_id)
            ->first();

        if (!$student) {
            return response()->json(['message' => 'Student profile not found'], 404);
        }

        return response()->json([
            'id' => $student->student_id,
            'studentNumber' => $student->student_number,
            'name' => trim($student->first_name . ' ' . ($student->middle_name ? $student->middle_name . ' ' : '') . $student->last_name),
            'email' => $user->email,
            'contact' => $student->contact_number,
            'program' => $student->course,
            'yearLevel' => $student->year_level,
            'section' => $student->section,
            'enrollmentStatus' => $student->enrollment_status ?? 'Pending',
            'dateEnrolled' => $student->date_enrolled,
        ]);
    }

    /**
     * Get student's clearances with ALL signatories per organization
     */
    public function getClearances(Request $request)
    {
        $user = $request->user();

        if ($user->user_type !== 'student') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $clearances = StudentClearance::where('student_id', $user->user_id)
            ->with(['items.organization.admins', 'items.approver', 'term'])
            ->orderBy('created_at', 'desc')
            ->get();

        $formattedClearances = [];

        // Get all unique organizations from clearance items
        $organizationIds = [];
        foreach ($clearances as $clearance) {
            foreach ($clearance->items as $item) {
                if (!in_array($item->org_id, $organizationIds)) {
                    $organizationIds[] = $item->org_id;
                }
            }
        }

        // Load all organizations with their admins
        $organizations = \App\Models\Organization::with('admins')
            ->whereIn('org_id', $organizationIds)
            ->get();

        foreach ($organizations as $organization) {
            // Get all admins (signatories) for this organization
            $admins = $organization->admins;

            // For each admin, check if there's a clearance item
            foreach ($admins as $admin) {
                // Find the clearance item for this org and this specific admin
                $clearanceItem = null;
                foreach ($clearances as $clearance) {
                    foreach ($clearance->items as $item) {
                        if ($item->org_id === $organization->org_id &&
                            ($item->approved_by === $admin->admin_id || $item->approved_by === null)) {
                            $clearanceItem = $item;
                            break 2;
                        }
                    }
                }

                // Determine status - if there's a clearance item with this admin as approver
                $status = 'Pending';
                $reviewedAt = null;
                $itemId = null;

                if ($clearanceItem) {
                    if ($clearanceItem->approved_by === $admin->admin_id) {
                        $status = ucfirst($clearanceItem->status);
                        $reviewedAt = $clearanceItem->approved_date;
                        $itemId = $clearanceItem->item_id;
                    }
                }

                $formattedClearances[] = [
                    'id' => $itemId,
                    'organization_id' => $organization->org_id,
                    'organization_name' => $organization->org_name,
                    'organization_type' => ucfirst(str_replace('_', ' ', $organization->org_type)),
                    'department' => $organization->department ?? '',
                    'signatory_name' => $admin->full_name,
                    'signatory_position' => $admin->position,
                    'status' => $status,
                    'submitted_at' => $clearanceItem->created_at ?? null,
                    'reviewed_at' => $reviewedAt,
                    'is_auto_approved' => $clearanceItem->is_auto_approved ?? false,
                ];
            }
        }

        return response()->json($formattedClearances);
    }

    /**
     * Get clearance summary/statistics for the student
     */
    public function getClearanceSummary(Request $request)
    {
        $user = $request->user();

        if ($user->user_type !== 'student') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $clearance = StudentClearance::where('student_id', $user->user_id)
            ->with('items')
            ->latest('created_at')
            ->first();

        if (!$clearance) {
            return response()->json([
                'total' => 0,
                'approved' => 0,
                'pending' => 0,
                'needs_compliance' => 0,
                'overall_status' => 'No Clearance'
            ]);
        }

        $items = $clearance->items;
        $approved = $items->where('status', 'Approved')->count();
        $pending = $items->where('status', 'Pending')->count();
        $needsCompliance = $items->where('status', 'Needs Compliance')->count();

        return response()->json([
            'total' => $items->count(),
            'approved' => $approved,
            'pending' => $pending,
            'needs_compliance' => $needsCompliance,
            'overall_status' => $clearance->overall_status ?? 'Pending',
            'clearance_status' => $approved === $items->count() ? 'Approved' : ($needsCompliance > 0 ? 'Needs Compliance' : 'Pending')
        ]);
    }
}
