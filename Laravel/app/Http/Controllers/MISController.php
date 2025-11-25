<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Student;
use App\Models\Organization;
use App\Models\OrganizationAdmin;
use App\Models\StudentClearance;
use App\Models\ClearanceItem;
use App\Models\AcademicTerm;
use App\Models\SystemAdmin;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class MISController extends Controller
{
    /**
     * Get dashboard statistics for MIS admin
     */
    public function dashboard(Request $request)
    {
        // Get system admin details
        $systemAdmin = SystemAdmin::where('sys_admin_id', $request->user()->user_id)
            ->with('user')
            ->first();

        if (!$systemAdmin) {
            return response()->json(['message' => 'System admin not found'], 404);
        }

        // Get current term
        $currentTerm = AcademicTerm::where('is_current', true)->first();

        // Count statistics
        $stats = [
            'total_students' => Student::count(),
            'active_students' => Student::whereHas('user', function ($query) {
                $query->where('is_active', true);
            })->count(),
            'total_organizations' => Organization::count(),
            'active_organizations' => Organization::where('is_active', true)->count(),
            'total_org_admins' => OrganizationAdmin::count(),
            'active_org_admins' => OrganizationAdmin::where('is_active', true)->count(),
            'total_clearances' => StudentClearance::count(),
        ];

        // Clearance statistics for current term
        if ($currentTerm) {
            $clearanceStats = StudentClearance::where('term_id', $currentTerm->term_id)
                ->select('overall_status', DB::raw('count(*) as count'))
                ->groupBy('overall_status')
                ->get()
                ->pluck('count', 'overall_status');

            $stats['current_term'] = [
                'term_id' => $currentTerm->term_id,
                'term_name' => $currentTerm->term_name,
                'academic_year' => $currentTerm->academic_year,
                'semester' => $currentTerm->semester,
                'total_clearances' => $clearanceStats->sum(),
                'approved' => $clearanceStats['approved'] ?? 0,
                'pending' => $clearanceStats['pending'] ?? 0,
                'incomplete' => $clearanceStats['incomplete'] ?? 0,
            ];
        } else {
            $stats['current_term'] = null;
        }

        // Recent clearance activity (last 10)
        $recentActivity = ClearanceItem::with([
            'clearance.student.user',
            'organization',
            'approver'
        ])
            ->whereNotNull('approved_date')
            ->orderBy('approved_date', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'item_id' => $item->item_id,
                    'student_name' => $item->clearance->student
                        ? trim(($item->clearance->student->first_name ?? '') . ' ' .
                               ($item->clearance->student->last_name ?? ''))
                        : null,
                    'student_number' => $item->clearance->student->student_number ?? null,
                    'organization_name' => $item->organization->org_name ?? null,
                    'status' => $item->status,
                    'approved_date' => $item->approved_date,
                ];
            });

        return response()->json([
            'admin_info' => [
                'full_name' => $systemAdmin->full_name,
                'admin_level' => $systemAdmin->admin_level,
                'department' => $systemAdmin->department,
            ],
            'statistics' => $stats,
            'recent_activity' => $recentActivity,
        ]);
    }

    // ==================== STUDENT MANAGEMENT ====================

    /**
     * Get all students
     */
    public function getStudents(Request $request)
    {
        $query = Student::with('user');

        // Filter by enrollment status
        if ($request->has('enrollment_status') && $request->enrollment_status !== 'all') {
            $query->where('enrollment_status', $request->enrollment_status);
        }

        // Filter by course
        if ($request->has('course') && $request->course !== '') {
            $query->where('course', 'like', '%' . $request->course . '%');
        }

        // Filter by year level
        if ($request->has('year_level') && $request->year_level !== '') {
            $query->where('year_level', $request->year_level);
        }

        // Filter by student type
        if ($request->has('student_type') && $request->student_type !== 'all') {
            $query->where('student_type', $request->student_type);
        }

        // Search by name, email, or student number
        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('student_number', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('email', 'like', "%{$search}%");
                  });
            });
        }

        // Get pagination parameters
        $perPage = $request->get('per_page', 15);
        $page = $request->get('page', 1);

        // Get total count
        $total = $query->count();

        // Get paginated results
        $students = $query->skip(($page - 1) * $perPage)
                          ->take($perPage)
                          ->get()
                          ->map(function ($student) {
            return [
                'student_id' => $student->student_id,
                'student_number' => $student->student_number,
                'first_name' => $student->first_name,
                'middle_name' => $student->middle_name,
                'last_name' => $student->last_name,
                'course' => $student->course,
                'year_level' => $student->year_level,
                'section' => $student->section,
                'contact_number' => $student->contact_number,
                'enrollment_status' => $student->enrollment_status,
                'student_type' => $student->student_type,
                'date_enrolled' => $student->date_enrolled,
                'user' => $student->user ? [
                    'user_id' => $student->user->user_id,
                    'username' => $student->user->username,
                    'email' => $student->user->email,
                    'user_type' => $student->user->user_type,
                    'is_active' => $student->user->is_active,
                ] : null,
                'username' => $student->user->username ?? null,
                'email' => $student->user->email ?? null,
            ];
        });

        return response()->json([
            'data' => $students,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => ceil($total / $perPage)
        ]);
    }

    /**
     * Get a specific student
     */
    public function getStudent($id)
    {
        $student = Student::with('user', 'clearances.term')->find($id);

        if (!$student) {
            return response()->json(['message' => 'Student not found'], 404);
        }

        return response()->json([
            'student_id' => $student->student_id,
            'student_number' => $student->student_number,
            'first_name' => $student->first_name,
            'middle_name' => $student->middle_name,
            'last_name' => $student->last_name,
            'course' => $student->course,
            'year_level' => $student->year_level,
            'section' => $student->section,
            'contact_number' => $student->contact_number,
            'enrollment_status' => $student->enrollment_status,
            'date_enrolled' => $student->date_enrolled,
            'email' => $student->user->email ?? null,
            'username' => $student->user->username ?? null,
            'is_active' => $student->user->is_active ?? null,
            'clearances' => $student->clearances,
        ]);
    }

    /**
     * Create a new student
     */
    public function createStudent(Request $request)
    {
        $request->validate([
            'username' => 'required|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'student_number' => 'required|unique:students,student_number',
            'first_name' => 'required',
            'last_name' => 'required',
            'course' => 'required',
            'year_level' => 'required|integer|min:1|max:5',
            'section' => 'nullable',
            'contact_number' => 'nullable',
            'enrollment_status' => 'required|in:enrolled,not_enrolled,graduated,dropped',
        ]);

        DB::beginTransaction();
        try {
            // Create user
            $user = User::create([
                'username' => $request->username,
                'email' => $request->email,
                'password_hash' => Hash::make($request->password),
                'user_type' => 'student',
                'is_active' => true,
            ]);

            // Create student
            $student = Student::create([
                'student_id' => $user->user_id,
                'student_number' => $request->student_number,
                'first_name' => $request->first_name,
                'middle_name' => $request->middle_name,
                'last_name' => $request->last_name,
                'course' => $request->course,
                'year_level' => $request->year_level,
                'section' => $request->section,
                'contact_number' => $request->contact_number,
                'date_enrolled' => now(),
                'enrollment_status' => $request->enrollment_status,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Student created successfully',
                'student' => $student->load('user'),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to create student', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update a student
     */
    public function updateStudent(Request $request, $id)
    {
        $student = Student::with('user')->find($id);

        if (!$student) {
            return response()->json(['message' => 'Student not found'], 404);
        }

        $request->validate([
            'email' => 'sometimes|email|unique:users,email,' . $student->user->user_id . ',user_id',
            'username' => 'sometimes|unique:users,username,' . $student->user->user_id . ',user_id',
            'password' => 'sometimes|min:6',
            'student_number' => 'sometimes|unique:students,student_number,' . $id . ',student_id',
            'first_name' => 'sometimes',
            'last_name' => 'sometimes',
            'course' => 'sometimes',
            'year_level' => 'sometimes|integer|min:1|max:5',
            'enrollment_status' => 'sometimes|in:enrolled,not_enrolled,graduated,dropped',
            'is_active' => 'sometimes|boolean',
        ]);

        DB::beginTransaction();
        try {
            // Update user fields
            $userUpdates = [];
            if ($request->has('email')) $userUpdates['email'] = $request->email;
            if ($request->has('username')) $userUpdates['username'] = $request->username;
            if ($request->has('password')) $userUpdates['password_hash'] = Hash::make($request->password);
            if ($request->has('is_active')) $userUpdates['is_active'] = $request->is_active;

            if (!empty($userUpdates)) {
                $student->user->update($userUpdates);
            }

            // Update student fields
            $studentUpdates = $request->only([
                'student_number', 'first_name', 'middle_name', 'last_name',
                'course', 'year_level', 'section', 'contact_number', 'enrollment_status'
            ]);

            if (!empty($studentUpdates)) {
                $student->update($studentUpdates);
            }

            DB::commit();

            return response()->json([
                'message' => 'Student updated successfully',
                'student' => $student->fresh('user'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to update student', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete a student
     */
    public function deleteStudent($id)
    {
        $student = Student::with('user')->find($id);

        if (!$student) {
            return response()->json(['message' => 'Student not found'], 404);
        }

        DB::beginTransaction();
        try {
            // Delete student record
            $student->delete();

            // Delete user record
            $student->user->delete();

            DB::commit();

            return response()->json(['message' => 'Student deleted successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to delete student', 'error' => $e->getMessage()], 500);
        }
    }

    // ==================== ORGANIZATION ADMIN MANAGEMENT ====================

    /**
     * Get all organization admins
     */
    public function getOrgAdmins(Request $request)
    {
        $query = OrganizationAdmin::with('user', 'organization');

        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        // Filter by organization
        if ($request->has('org_id')) {
            $query->where('org_id', $request->org_id);
        }

        // Get total count
        $total = $query->count();

        // Get pagination parameters
        $perPage = $request->get('per_page', 15);
        $page = $request->get('page', 1);

        // Get paginated results
        $orgAdmins = $query->skip(($page - 1) * $perPage)
                           ->take($perPage)
                           ->get()
                           ->map(function ($orgAdmin) {
            return [
                'admin_id' => $orgAdmin->admin_id,
                'org_id' => $orgAdmin->org_id,
                'org_name' => $orgAdmin->organization->org_name ?? null,
                'org_code' => $orgAdmin->organization->org_code ?? null,
                'position' => $orgAdmin->position,
                'full_name' => $orgAdmin->full_name,
                'email' => $orgAdmin->user->email ?? null,
                'username' => $orgAdmin->user->username ?? null,
                'is_active' => $orgAdmin->is_active,
                'assigned_date' => $orgAdmin->assigned_date,
            ];
        });

        return response()->json([
            'data' => $orgAdmins,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => ceil($total / $perPage)
        ]);
    }

    /**
     * Get a specific organization admin
     */
    public function getOrgAdmin($id)
    {
        $orgAdmin = OrganizationAdmin::with('user', 'organization')->find($id);

        if (!$orgAdmin) {
            return response()->json(['message' => 'Organization admin not found'], 404);
        }

        return response()->json([
            'admin_id' => $orgAdmin->admin_id,
            'org_id' => $orgAdmin->org_id,
            'org_name' => $orgAdmin->organization->org_name ?? null,
            'org_code' => $orgAdmin->organization->org_code ?? null,
            'position' => $orgAdmin->position,
            'full_name' => $orgAdmin->full_name,
            'email' => $orgAdmin->user->email ?? null,
            'username' => $orgAdmin->user->username ?? null,
            'is_active' => $orgAdmin->is_active,
            'assigned_date' => $orgAdmin->assigned_date,
            'removed_date' => $orgAdmin->removed_date,
        ]);
    }

    /**
     * Create a new organization admin
     */
    public function createOrgAdmin(Request $request)
    {
        $request->validate([
            'username' => 'required|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'org_id' => 'required|exists:organizations,org_id',
            'position' => 'required',
            'full_name' => 'required',
        ]);

        // Check if organization already has an admin
        $existingAdmin = OrganizationAdmin::where('org_id', $request->org_id)->first();
        if ($existingAdmin) {
            return response()->json(['message' => 'This organization already has an admin assigned'], 422);
        }

        DB::beginTransaction();
        try {
            // Create user
            $user = User::create([
                'username' => $request->username,
                'email' => $request->email,
                'password_hash' => Hash::make($request->password),
                'user_type' => 'org_admin',
                'is_active' => true,
            ]);

            // Create organization admin
            $orgAdmin = OrganizationAdmin::create([
                'admin_id' => $user->user_id,
                'org_id' => $request->org_id,
                'position' => $request->position,
                'full_name' => $request->full_name,
                'is_active' => true,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Organization admin created successfully',
                'org_admin' => $orgAdmin->load('user', 'organization'),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to create organization admin', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update an organization admin
     */
    public function updateOrgAdmin(Request $request, $id)
    {
        $orgAdmin = OrganizationAdmin::with('user')->find($id);

        if (!$orgAdmin) {
            return response()->json(['message' => 'Organization admin not found'], 404);
        }

        $request->validate([
            'email' => 'sometimes|email|unique:users,email,' . $orgAdmin->user->user_id . ',user_id',
            'username' => 'sometimes|unique:users,username,' . $orgAdmin->user->user_id . ',user_id',
            'password' => 'sometimes|min:6',
            'org_id' => 'sometimes|exists:organizations,org_id',
            'position' => 'sometimes',
            'full_name' => 'sometimes',
            'is_active' => 'sometimes|boolean',
        ]);

        // Check if changing org_id to one that already has an admin
        if ($request->has('org_id') && $request->org_id != $orgAdmin->org_id) {
            $existingAdmin = OrganizationAdmin::where('org_id', $request->org_id)->first();
            if ($existingAdmin) {
                return response()->json(['message' => 'This organization already has an admin assigned'], 422);
            }
        }

        DB::beginTransaction();
        try {
            // Update user fields
            $userUpdates = [];
            if ($request->has('email')) $userUpdates['email'] = $request->email;
            if ($request->has('username')) $userUpdates['username'] = $request->username;
            if ($request->has('password')) $userUpdates['password_hash'] = Hash::make($request->password);
            if ($request->has('is_active')) $userUpdates['is_active'] = $request->is_active;

            if (!empty($userUpdates)) {
                $orgAdmin->user->update($userUpdates);
            }

            // Update organization admin fields
            $orgAdminUpdates = $request->only(['org_id', 'position', 'full_name', 'is_active']);

            if (!empty($orgAdminUpdates)) {
                $orgAdmin->update($orgAdminUpdates);
            }

            DB::commit();

            return response()->json([
                'message' => 'Organization admin updated successfully',
                'org_admin' => $orgAdmin->fresh(['user', 'organization']),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to update organization admin', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete an organization admin
     */
    public function deleteOrgAdmin($id)
    {
        $orgAdmin = OrganizationAdmin::with('user')->find($id);

        if (!$orgAdmin) {
            return response()->json(['message' => 'Organization admin not found'], 404);
        }

        DB::beginTransaction();
        try {
            // Delete org admin record
            $orgAdmin->delete();

            // Delete user record
            $orgAdmin->user->delete();

            DB::commit();

            return response()->json(['message' => 'Organization admin deleted successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to delete organization admin', 'error' => $e->getMessage()], 500);
        }
    }

    // ==================== SYSTEM ADMIN MANAGEMENT ====================

    /**
     * Get all system admins
     */
    public function getSysAdmins(Request $request)
    {
        $query = SystemAdmin::with('user');

        // Filter by admin level
        if ($request->has('admin_level')) {
            $query->where('admin_level', $request->admin_level);
        }

        // Get total count
        $total = $query->count();

        // Get pagination parameters
        $perPage = $request->get('per_page', 15);
        $page = $request->get('page', 1);

        // Get paginated results
        $sysAdmins = $query->skip(($page - 1) * $perPage)
                           ->take($perPage)
                           ->get()
                           ->map(function ($sysAdmin) {
            return [
                'sys_admin_id' => $sysAdmin->sys_admin_id,
                'admin_level' => $sysAdmin->admin_level,
                'full_name' => $sysAdmin->full_name,
                'department' => $sysAdmin->department,
                'email' => $sysAdmin->user->email ?? null,
                'username' => $sysAdmin->user->username ?? null,
                'is_active' => $sysAdmin->user->is_active ?? null,
                'assigned_date' => $sysAdmin->assigned_date,
            ];
        });

        return response()->json([
            'data' => $sysAdmins,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => ceil($total / $perPage)
        ]);
    }

    /**
     * Get a specific system admin
     */
    public function getSysAdmin($id)
    {
        $sysAdmin = SystemAdmin::with('user')->find($id);

        if (!$sysAdmin) {
            return response()->json(['message' => 'System admin not found'], 404);
        }

        return response()->json([
            'sys_admin_id' => $sysAdmin->sys_admin_id,
            'admin_level' => $sysAdmin->admin_level,
            'full_name' => $sysAdmin->full_name,
            'department' => $sysAdmin->department,
            'email' => $sysAdmin->user->email ?? null,
            'username' => $sysAdmin->user->username ?? null,
            'is_active' => $sysAdmin->user->is_active ?? null,
            'assigned_date' => $sysAdmin->assigned_date,
        ]);
    }

    /**
     * Create a new system admin
     */
    public function createSysAdmin(Request $request)
    {
        $request->validate([
            'username' => 'required|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'admin_level' => 'required|in:super_admin,mis_staff',
            'full_name' => 'required',
            'department' => 'required',
        ]);

        DB::beginTransaction();
        try {
            // Create user
            $user = User::create([
                'username' => $request->username,
                'email' => $request->email,
                'password_hash' => Hash::make($request->password),
                'user_type' => 'sys_admin',
                'is_active' => true,
            ]);

            // Create system admin
            $sysAdmin = SystemAdmin::create([
                'sys_admin_id' => $user->user_id,
                'admin_level' => $request->admin_level,
                'full_name' => $request->full_name,
                'department' => $request->department,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'System admin created successfully',
                'sys_admin' => $sysAdmin->load('user'),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to create system admin', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update a system admin
     */
    public function updateSysAdmin(Request $request, $id)
    {
        $sysAdmin = SystemAdmin::with('user')->find($id);

        if (!$sysAdmin) {
            return response()->json(['message' => 'System admin not found'], 404);
        }

        $request->validate([
            'email' => 'sometimes|email|unique:users,email,' . $sysAdmin->user->user_id . ',user_id',
            'username' => 'sometimes|unique:users,username,' . $sysAdmin->user->user_id . ',user_id',
            'password' => 'sometimes|min:6',
            'admin_level' => 'sometimes|in:super_admin,mis_staff',
            'full_name' => 'sometimes',
            'department' => 'sometimes',
            'is_active' => 'sometimes|boolean',
        ]);

        DB::beginTransaction();
        try {
            // Update user fields
            $userUpdates = [];
            if ($request->has('email')) $userUpdates['email'] = $request->email;
            if ($request->has('username')) $userUpdates['username'] = $request->username;
            if ($request->has('password')) $userUpdates['password_hash'] = Hash::make($request->password);
            if ($request->has('is_active')) $userUpdates['is_active'] = $request->is_active;

            if (!empty($userUpdates)) {
                $sysAdmin->user->update($userUpdates);
            }

            // Update system admin fields
            $sysAdminUpdates = $request->only(['admin_level', 'full_name', 'department']);

            if (!empty($sysAdminUpdates)) {
                $sysAdmin->update($sysAdminUpdates);
            }

            DB::commit();

            return response()->json([
                'message' => 'System admin updated successfully',
                'sys_admin' => $sysAdmin->fresh('user'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to update system admin', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete a system admin
     */
    public function deleteSysAdmin($id)
    {
        $sysAdmin = SystemAdmin::with('user')->find($id);

        if (!$sysAdmin) {
            return response()->json(['message' => 'System admin not found'], 404);
        }

        // Prevent deleting yourself
        if (request()->user()->user_id == $id) {
            return response()->json(['message' => 'You cannot delete your own account'], 422);
        }

        DB::beginTransaction();
        try {
            // Delete sys admin record
            $sysAdmin->delete();

            // Delete user record
            $sysAdmin->user->delete();

            DB::commit();

            return response()->json(['message' => 'System admin deleted successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to delete system admin', 'error' => $e->getMessage()], 500);
        }
    }

    // ==================== ORGANIZATION MANAGEMENT ====================

    /**
     * Get all organizations
     */
    public function getOrganizations(Request $request)
    {
        $query = Organization::with('admin.user');

        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        // Filter by organization type
        if ($request->has('org_type')) {
            $query->where('org_type', $request->org_type);
        }

        // Filter by requires clearance
        if ($request->has('requires_clearance')) {
            $query->where('requires_clearance', $request->requires_clearance);
        }

        // Search by name or code
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('org_name', 'like', "%{$search}%")
                  ->orWhere('org_code', 'like', "%{$search}%");
            });
        }

        // Get total count
        $total = $query->count();

        // Get pagination parameters
        $perPage = $request->get('per_page', 15);
        $page = $request->get('page', 1);

        // Get paginated results
        $organizations = $query->skip(($page - 1) * $perPage)
                               ->take($perPage)
                               ->get()
                               ->map(function ($org) {
            return [
                'org_id' => $org->org_id,
                'org_code' => $org->org_code,
                'org_name' => $org->org_name,
                'org_type' => $org->org_type,
                'department' => $org->department,
                'is_active' => $org->is_active,
                'requires_clearance' => $org->requires_clearance,
                'created_at' => $org->created_at,
                'admin_name' => $org->admin->full_name ?? null,
                'admin_email' => $org->admin->user->email ?? null,
            ];
        });

        return response()->json([
            'data' => $organizations,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => ceil($total / $perPage)
        ]);
    }

    /**
     * Get a specific organization
     */
    public function getOrganization($id)
    {
        $org = Organization::with('admin.user', 'clearanceItems')->find($id);

        if (!$org) {
            return response()->json(['message' => 'Organization not found'], 404);
        }

        // Get clearance statistics for this organization
        $clearanceStats = ClearanceItem::where('org_id', $id)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status');

        return response()->json([
            'org_id' => $org->org_id,
            'org_code' => $org->org_code,
            'org_name' => $org->org_name,
            'org_type' => $org->org_type,
            'department' => $org->department,
            'is_active' => $org->is_active,
            'requires_clearance' => $org->requires_clearance,
            'created_at' => $org->created_at,
            'admin_name' => $org->admin->full_name ?? null,
            'admin_email' => $org->admin->user->email ?? null,
            'admin_position' => $org->admin->position ?? null,
            'clearance_stats' => [
                'total' => $clearanceStats->sum(),
                'approved' => $clearanceStats['approved'] ?? 0,
                'pending' => $clearanceStats['pending'] ?? 0,
                'needs_compliance' => $clearanceStats['needs_compliance'] ?? 0,
            ],
        ]);
    }

    /**
     * Create a new organization
     */
    public function createOrganization(Request $request)
    {
        $request->validate([
            'org_code' => 'required|unique:organizations,org_code|max:20',
            'org_name' => 'required|max:100',
            'org_type' => 'required|in:academic,administrative,finance,student_services',
            'department' => 'nullable|max:100',
            'requires_clearance' => 'required|boolean',
        ]);

        try {
            $organization = Organization::create([
                'org_code' => $request->org_code,
                'org_name' => $request->org_name,
                'org_type' => $request->org_type,
                'department' => $request->department,
                'is_active' => true,
                'requires_clearance' => $request->requires_clearance,
            ]);

            return response()->json([
                'message' => 'Organization created successfully',
                'organization' => $organization,
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to create organization', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update an organization
     */
    public function updateOrganization(Request $request, $id)
    {
        $org = Organization::find($id);

        if (!$org) {
            return response()->json(['message' => 'Organization not found'], 404);
        }

        $request->validate([
            'org_code' => 'sometimes|unique:organizations,org_code,' . $id . ',org_id|max:20',
            'org_name' => 'sometimes|max:100',
            'org_type' => 'sometimes|in:academic,administrative,finance,student_services',
            'department' => 'nullable|max:100',
            'is_active' => 'sometimes|boolean',
            'requires_clearance' => 'sometimes|boolean',
        ]);

        try {
            $org->update($request->only([
                'org_code', 'org_name', 'org_type', 'department', 'is_active', 'requires_clearance'
            ]));

            return response()->json([
                'message' => 'Organization updated successfully',
                'organization' => $org->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update organization', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete an organization
     */
    public function deleteOrganization($id)
    {
        $org = Organization::with('admin', 'clearanceItems')->find($id);

        if (!$org) {
            return response()->json(['message' => 'Organization not found'], 404);
        }

        // Check if organization has clearance items
        if ($org->clearanceItems()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete organization with existing clearance items. Please deactivate instead.'
            ], 422);
        }

        // Check if organization has an admin
        if ($org->admin) {
            return response()->json([
                'message' => 'Cannot delete organization with an assigned admin. Please remove the admin first.'
            ], 422);
        }

        try {
            $org->delete();

            return response()->json(['message' => 'Organization deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to delete organization', 'error' => $e->getMessage()], 500);
        }
    }

    // ==================== CLEARANCE MONITORING ====================

    /**
     * Get all clearances with filtering
     */
    public function getClearances(Request $request)
    {
        $query = StudentClearance::with(['student.user', 'term', 'items.organization']);

        // Filter by overall status
        if ($request->has('overall_status')) {
            $query->where('overall_status', $request->overall_status);
        }

        // Filter by term
        if ($request->has('term_id')) {
            $query->where('term_id', $request->term_id);
        }

        // Filter by locked status
        if ($request->has('is_locked')) {
            $query->where('is_locked', $request->is_locked);
        }

        // Search by student name or number
        if ($request->has('search')) {
            $search = $request->search;
            $query->whereHas('student', function ($q) use ($search) {
                $q->where('student_number', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        // Order by most recent first
        $query->orderBy('created_at', 'desc');

        // Get total count
        $total = $query->count();

        // Get pagination parameters
        $perPage = $request->get('per_page', 15);
        $page = $request->get('page', 1);

        // Get paginated results
        $clearances = $query->skip(($page - 1) * $perPage)
                            ->take($perPage)
                            ->get()
                            ->map(function ($clearance) {
            $student = $clearance->student;
            return [
                'clearance_id' => $clearance->clearance_id,
                'student_id' => $clearance->student_id,
                'student_number' => $student->student_number ?? null,
                'student_name' => $student
                    ? trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? ''))
                    : null,
                'course' => $student->course ?? null,
                'year_level' => $student->year_level ?? null,
                'term_name' => $clearance->term->term_name ?? null,
                'academic_year' => $clearance->term->academic_year ?? null,
                'overall_status' => $clearance->overall_status,
                'is_locked' => $clearance->is_locked,
                'created_at' => $clearance->created_at,
                'approved_date' => $clearance->approved_date,
                'total_items' => $clearance->items->count(),
                'approved_items' => $clearance->items->where('status', 'approved')->count(),
            ];
        });

        return response()->json([
            'data' => $clearances,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => ceil($total / $perPage)
        ]);
    }

    /**
     * Get a specific clearance with all details
     */
    public function getClearance($id)
    {
        $clearance = StudentClearance::with([
            'student.user',
            'term',
            'items.organization',
            'items.approver'
        ])->find($id);

        if (!$clearance) {
            return response()->json(['message' => 'Clearance not found'], 404);
        }

        $student = $clearance->student;

        return response()->json([
            'clearance_id' => $clearance->clearance_id,
            'student' => [
                'student_id' => $student->student_id,
                'student_number' => $student->student_number,
                'first_name' => $student->first_name,
                'middle_name' => $student->middle_name,
                'last_name' => $student->last_name,
                'course' => $student->course,
                'year_level' => $student->year_level,
                'section' => $student->section,
                'email' => $student->user->email ?? null,
            ],
            'term' => [
                'term_id' => $clearance->term->term_id,
                'term_name' => $clearance->term->term_name,
                'academic_year' => $clearance->term->academic_year,
                'semester' => $clearance->term->semester,
                'clearance_deadline' => $clearance->term->clearance_deadline,
            ],
            'overall_status' => $clearance->overall_status,
            'is_locked' => $clearance->is_locked,
            'created_at' => $clearance->created_at,
            'approved_date' => $clearance->approved_date,
            'items' => $clearance->items->map(function ($item) {
                return [
                    'item_id' => $item->item_id,
                    'organization' => [
                        'org_id' => $item->organization->org_id,
                        'org_code' => $item->organization->org_code,
                        'org_name' => $item->organization->org_name,
                    ],
                    'status' => $item->status,
                    'approved_by' => $item->approver->full_name ?? null,
                    'approved_date' => $item->approved_date,
                    'is_auto_approved' => $item->is_auto_approved,
                ];
            }),
        ]);
    }

    /**
     * Get clearance items across all organizations with filtering
     */
    public function getClearanceItems(Request $request)
    {
        $query = ClearanceItem::with([
            'clearance.student.user',
            'clearance.term',
            'organization',
            'approver'
        ]);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by organization
        if ($request->has('org_id')) {
            $query->where('org_id', $request->org_id);
        }

        // Filter by term
        if ($request->has('term_id')) {
            $query->whereHas('clearance', function ($q) use ($request) {
                $q->where('term_id', $request->term_id);
            });
        }

        // Search by student name or number
        if ($request->has('search')) {
            $search = $request->search;
            $query->whereHas('clearance.student', function ($q) use ($search) {
                $q->where('student_number', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        $query->orderBy('status_updated', 'desc');

        $items = $query->get()->map(function ($item) {
            $student = $item->clearance->student;
            return [
                'item_id' => $item->item_id,
                'clearance_id' => $item->clearance_id,
                'student_number' => $student->student_number ?? null,
                'student_name' => $student
                    ? trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? ''))
                    : null,
                'organization_name' => $item->organization->org_name ?? null,
                'organization_code' => $item->organization->org_code ?? null,
                'status' => $item->status,
                'approved_by' => $item->approver->full_name ?? null,
                'approved_date' => $item->approved_date,
                'is_auto_approved' => $item->is_auto_approved,
                'term_name' => $item->clearance->term->term_name ?? null,
            ];
        });

        return response()->json($items);
    }

    /**
     * Get clearance statistics by organization
     */
    public function getClearanceStatsByOrg(Request $request)
    {
        $termId = $request->get('term_id');

        $query = ClearanceItem::with('organization');

        if ($termId) {
            $query->whereHas('clearance', function ($q) use ($termId) {
                $q->where('term_id', $termId);
            });
        }

        $items = $query->get();

        $statsByOrg = $items->groupBy('org_id')->map(function ($orgItems, $orgId) {
            $org = $orgItems->first()->organization;
            $totalItems = $orgItems->count();
            $approvedItems = $orgItems->where('status', 'approved')->count();
            $pendingItems = $orgItems->where('status', 'pending')->count();
            $needsComplianceItems = $orgItems->where('status', 'needs_compliance')->count();

            return [
                'org_id' => $orgId,
                'org_name' => $org->org_name,
                'org_code' => $org->org_code,
                'total' => $totalItems,
                'approved' => $approvedItems,
                'pending' => $pendingItems,
                'needs_compliance' => $needsComplianceItems,
                'approval_rate' => $totalItems > 0 ? round(($approvedItems / $totalItems) * 100, 2) : 0,
            ];
        })->values();

        return response()->json($statsByOrg);
    }

    // ==================== ACADEMIC TERM MANAGEMENT ====================

    /**
     * Get all academic terms
     */
    public function getTerms(Request $request)
    {
        $query = AcademicTerm::query();

        // Filter by current term
        if ($request->has('is_current')) {
            $query->where('is_current', $request->is_current);
        }

        // Filter by semester
        if ($request->has('semester')) {
            $query->where('semester', $request->semester);
        }

        // Order by most recent first
        $query->orderBy('start_date', 'desc');

        // Get total count
        $total = $query->count();

        // Get pagination parameters
        $perPage = $request->get('per_page', 15);
        $page = $request->get('page', 1);

        // Get paginated results
        $terms = $query->skip(($page - 1) * $perPage)
                       ->take($perPage)
                       ->get()
                       ->map(function ($term) {
            $clearanceCount = StudentClearance::where('term_id', $term->term_id)->count();

            return [
                'term_id' => $term->term_id,
                'academic_year' => $term->academic_year,
                'semester' => $term->semester,
                'term_name' => $term->term_name,
                'start_date' => $term->start_date,
                'end_date' => $term->end_date,
                'enrollment_start' => $term->enrollment_start,
                'enrollment_end' => $term->enrollment_end,
                'clearance_deadline' => $term->clearance_deadline,
                'is_current' => $term->is_current,
                'created_at' => $term->created_at,
                'clearance_count' => $clearanceCount,
            ];
        });

        return response()->json([
            'data' => $terms,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => ceil($total / $perPage)
        ]);
    }

    /**
     * Get a specific academic term
     */
    public function getTerm($id)
    {
        $term = AcademicTerm::find($id);

        if (!$term) {
            return response()->json(['message' => 'Academic term not found'], 404);
        }

        // Get clearance statistics for this term
        $clearanceStats = StudentClearance::where('term_id', $id)
            ->select('overall_status', DB::raw('count(*) as count'))
            ->groupBy('overall_status')
            ->get()
            ->pluck('count', 'overall_status');

        return response()->json([
            'term_id' => $term->term_id,
            'academic_year' => $term->academic_year,
            'semester' => $term->semester,
            'term_name' => $term->term_name,
            'start_date' => $term->start_date,
            'end_date' => $term->end_date,
            'enrollment_start' => $term->enrollment_start,
            'enrollment_end' => $term->enrollment_end,
            'clearance_deadline' => $term->clearance_deadline,
            'is_current' => $term->is_current,
            'created_at' => $term->created_at,
            'clearance_stats' => [
                'total' => $clearanceStats->sum(),
                'approved' => $clearanceStats['approved'] ?? 0,
                'pending' => $clearanceStats['pending'] ?? 0,
                'incomplete' => $clearanceStats['incomplete'] ?? 0,
            ],
        ]);
    }

    /**
     * Create a new academic term
     */
    public function createTerm(Request $request)
    {
        $request->validate([
            'academic_year' => 'required|max:20',
            'semester' => 'required|in:first,second,summer',
            'term_name' => 'required|max:50',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'enrollment_start' => 'nullable|date',
            'enrollment_end' => 'nullable|date|after:enrollment_start',
            'clearance_deadline' => 'nullable|date',
        ]);

        try {
            $term = AcademicTerm::create([
                'academic_year' => $request->academic_year,
                'semester' => $request->semester,
                'term_name' => $request->term_name,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'enrollment_start' => $request->enrollment_start,
                'enrollment_end' => $request->enrollment_end,
                'clearance_deadline' => $request->clearance_deadline,
                'is_current' => false,
            ]);

            return response()->json([
                'message' => 'Academic term created successfully',
                'term' => $term,
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to create academic term', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update an academic term
     */
    public function updateTerm(Request $request, $id)
    {
        $term = AcademicTerm::find($id);

        if (!$term) {
            return response()->json(['message' => 'Academic term not found'], 404);
        }

        $request->validate([
            'academic_year' => 'sometimes|max:20',
            'semester' => 'sometimes|in:first,second,summer',
            'term_name' => 'sometimes|max:50',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date',
            'enrollment_start' => 'nullable|date',
            'enrollment_end' => 'nullable|date',
            'clearance_deadline' => 'nullable|date',
        ]);

        try {
            $term->update($request->only([
                'academic_year', 'semester', 'term_name', 'start_date', 'end_date',
                'enrollment_start', 'enrollment_end', 'clearance_deadline'
            ]));

            return response()->json([
                'message' => 'Academic term updated successfully',
                'term' => $term->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update academic term', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete an academic term
     */
    public function deleteTerm($id)
    {
        $term = AcademicTerm::find($id);

        if (!$term) {
            return response()->json(['message' => 'Academic term not found'], 404);
        }

        // Check if term has clearances
        $clearanceCount = StudentClearance::where('term_id', $id)->count();
        if ($clearanceCount > 0) {
            return response()->json([
                'message' => 'Cannot delete academic term with existing clearances.'
            ], 422);
        }

        // Prevent deleting current term
        if ($term->is_current) {
            return response()->json([
                'message' => 'Cannot delete the current active term. Please set another term as current first.'
            ], 422);
        }

        try {
            $term->delete();

            return response()->json(['message' => 'Academic term deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to delete academic term', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Set a term as the current active term
     */
    public function setCurrentTerm(Request $request, $id)
    {
        $term = AcademicTerm::find($id);

        if (!$term) {
            return response()->json(['message' => 'Academic term not found'], 404);
        }

        DB::beginTransaction();
        try {
            // Set all terms to not current
            AcademicTerm::query()->update(['is_current' => false]);

            // Set the specified term as current
            $term->update(['is_current' => true]);

            DB::commit();

            return response()->json([
                'message' => 'Current term updated successfully',
                'term' => $term->fresh(),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to set current term', 'error' => $e->getMessage()], 500);
        }
    }

    // ==================== AUDIT LOGS ====================

    /**
     * Get all audit logs with filtering
     */
    public function getAuditLogs(Request $request)
    {
        $query = AuditLog::with('user');

        // Filter by action type
        if ($request->has('action_type')) {
            $query->where('action_type', $request->action_type);
        }

        // Filter by table name
        if ($request->has('table_name')) {
            $query->where('table_name', $request->table_name);
        }

        // Filter by user
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by date range
        if ($request->has('start_date')) {
            $query->where('created_at', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->where('created_at', '<=', $request->end_date);
        }

        // Order by most recent first
        $query->orderBy('created_at', 'desc');

        // Paginate results (50 per page)
        $perPage = $request->get('per_page', 50);
        $logs = $query->paginate($perPage);

        return response()->json([
            'data' => $logs->items(),
            'pagination' => [
                'current_page' => $logs->currentPage(),
                'last_page' => $logs->lastPage(),
                'per_page' => $logs->perPage(),
                'total' => $logs->total(),
            ],
        ]);
    }

    /**
     * Get audit logs for a specific user
     */
    public function getUserAuditLogs($userId)
    {
        $user = User::find($userId);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $logs = AuditLog::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get();

        return response()->json([
            'user' => [
                'user_id' => $user->user_id,
                'username' => $user->username,
                'email' => $user->email,
                'user_type' => $user->user_type,
            ],
            'logs' => $logs,
        ]);
    }

    /**
     * Get audit log statistics
     */
    public function getAuditLogStats(Request $request)
    {
        $query = AuditLog::query();

        // Filter by date range if provided
        if ($request->has('start_date')) {
            $query->where('created_at', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->where('created_at', '<=', $request->end_date);
        }

        // Get statistics by action type
        $actionStats = (clone $query)
            ->select('action_type', DB::raw('count(*) as count'))
            ->groupBy('action_type')
            ->get()
            ->pluck('count', 'action_type');

        // Get statistics by table
        $tableStats = (clone $query)
            ->select('table_name', DB::raw('count(*) as count'))
            ->whereNotNull('table_name')
            ->groupBy('table_name')
            ->get()
            ->pluck('count', 'table_name');

        // Get most active users
        $activeUsers = (clone $query)
            ->with('user')
            ->select('user_id', DB::raw('count(*) as action_count'))
            ->whereNotNull('user_id')
            ->groupBy('user_id')
            ->orderBy('action_count', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($log) {
                return [
                    'user_id' => $log->user_id,
                    'username' => $log->user->username ?? null,
                    'email' => $log->user->email ?? null,
                    'action_count' => $log->action_count,
                ];
            });

        return response()->json([
            'total_logs' => $query->count(),
            'by_action_type' => $actionStats,
            'by_table' => $tableStats,
            'most_active_users' => $activeUsers,
        ]);
    }
}
