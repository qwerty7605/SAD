<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\OrgAdminClearanceController;
use App\Http\Controllers\MISController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('logout', [AuthController::class, 'logout']);

// Organization Admin routes
Route::middleware('auth:sanctum')->prefix('org-admin')->group(function () {
    Route::get('clearance-items', [OrgAdminClearanceController::class, 'index']);
    Route::patch('clearance-items/{itemId}/approve', [OrgAdminClearanceController::class, 'approve']);
    Route::patch('clearance-items/{itemId}/needs-compliance', [OrgAdminClearanceController::class, 'needsCompliance']);
    Route::post('clearance-items/bulk-approve', [OrgAdminClearanceController::class, 'bulkApprove']);
    Route::get('statistics', [OrgAdminClearanceController::class, 'statistics']);
});

// MIS (System Admin) routes
Route::middleware(['auth:sanctum', 'sys_admin'])->prefix('mis')->group(function () {
    Route::get('dashboard', [MISController::class, 'dashboard']);

    // Student Management
    Route::get('students', [MISController::class, 'getStudents']);
    Route::get('students/{id}', [MISController::class, 'getStudent']);
    Route::post('students', [MISController::class, 'createStudent']);
    Route::put('students/{id}', [MISController::class, 'updateStudent']);
    Route::delete('students/{id}', [MISController::class, 'deleteStudent']);

    // Organization Admin Management
    Route::get('org-admins', [MISController::class, 'getOrgAdmins']);
    Route::get('org-admins/{id}', [MISController::class, 'getOrgAdmin']);
    Route::post('org-admins', [MISController::class, 'createOrgAdmin']);
    Route::put('org-admins/{id}', [MISController::class, 'updateOrgAdmin']);
    Route::delete('org-admins/{id}', [MISController::class, 'deleteOrgAdmin']);

    // System Admin Management
    Route::get('sys-admins', [MISController::class, 'getSysAdmins']);
    Route::get('sys-admins/{id}', [MISController::class, 'getSysAdmin']);
    Route::post('sys-admins', [MISController::class, 'createSysAdmin']);
    Route::put('sys-admins/{id}', [MISController::class, 'updateSysAdmin']);
    Route::delete('sys-admins/{id}', [MISController::class, 'deleteSysAdmin']);

    // Organization Management
    Route::get('organizations', [MISController::class, 'getOrganizations']);
    Route::get('organizations/{id}', [MISController::class, 'getOrganization']);
    Route::post('organizations', [MISController::class, 'createOrganization']);
    Route::put('organizations/{id}', [MISController::class, 'updateOrganization']);
    Route::delete('organizations/{id}', [MISController::class, 'deleteOrganization']);

    // Clearance Monitoring
    Route::get('clearances/stats-by-organization', [MISController::class, 'getClearanceStatsByOrg']);
    Route::get('clearances', [MISController::class, 'getClearances']);
    Route::get('clearances/{id}', [MISController::class, 'getClearance']);
    Route::get('clearances/{id}/items', [MISController::class, 'getClearanceItems']);

    // Academic Term Management
    Route::get('terms', [MISController::class, 'getTerms']);
    Route::get('terms/{id}', [MISController::class, 'getTerm']);
    Route::post('terms', [MISController::class, 'createTerm']);
    Route::put('terms/{id}', [MISController::class, 'updateTerm']);
    Route::delete('terms/{id}', [MISController::class, 'deleteTerm']);
    Route::post('terms/{id}/set-current', [MISController::class, 'setCurrentTerm']);

    // Audit Logs
    Route::get('audit-logs/stats', [MISController::class, 'getAuditLogStats']);
    Route::get('audit-logs/user/{userId}', [MISController::class, 'getUserAuditLogs']);
    Route::get('audit-logs', [MISController::class, 'getAuditLogs']);
});