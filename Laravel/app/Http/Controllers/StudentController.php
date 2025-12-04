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
     * Each clearance item now corresponds to a specific signatory
     */
    public function getClearances(Request $request)
    {
        $user = $request->user();

        if ($user->user_type !== 'student') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Get the most recent clearance for this student
        $clearance = StudentClearance::where('student_id', $user->user_id)
            ->with([
                'items.organization',
                'items.requiredSignatory',
                'items.approver',
                'term'
            ])
            ->latest('created_at')
            ->first();

        if (!$clearance) {
            return response()->json([]);
        }

        $formattedClearances = [];

        // Each clearance item now represents a specific signatory requirement
        foreach ($clearance->items as $item) {
            $signatory = $item->requiredSignatory;
            $approver = $item->approver;

            // Skip if signatory was deleted (shouldn't happen with proper FK constraints)
            if (!$signatory) {
                continue;
            }

            $formattedClearances[] = [
                'id' => $item->item_id,
                'organization_id' => $item->organization->org_id,
                'organization_name' => $item->organization->org_name,
                'organization_type' => ucfirst(str_replace('_', ' ', $item->organization->org_type)),
                'department' => $item->organization->department ?? '',
                'signatory_name' => $signatory->full_name,
                'signatory_position' => $signatory->position ?? 'Not Specified',
                'status' => ucwords(str_replace('_', ' ', $item->status)),  // Convert needs_compliance to Needs Compliance
                'submitted_at' => $item->created_at,
                'reviewed_at' => $item->approved_date,
                'is_auto_approved' => $item->is_auto_approved,
                'reviewed_by_name' => $approver ? $approver->full_name : null,  // More accurate name - handles both approval and needs_compliance
            ];
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
        $approved = $items->where('status', 'approved')->count();
        $pending = $items->where('status', 'pending')->count();
        $needsCompliance = $items->where('status', 'needs_compliance')->count();

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
