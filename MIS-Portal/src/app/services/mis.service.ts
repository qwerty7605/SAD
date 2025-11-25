import { Injectable, inject } from '@angular/core';
import { Observable } from 'rxjs';
import { ApiService } from './api.service';
import { Student, StudentFormData } from '../models/student.model';
import { OrgAdmin, OrgAdminFormData } from '../models/org-admin.model';
import { SysAdmin, SysAdminFormData } from '../models/sys-admin.model';
import { Organization, OrganizationFormData } from '../models/organization.model';
import { AcademicTerm, AcademicTermFormData } from '../models/term.model';
import { Clearance, ClearanceItem, OrganizationClearanceStats } from '../models/clearance.model';
import { AuditLog, AuditLogStats } from '../models/audit-log.model';

export interface DashboardStats {
  admin_info: {
    full_name: string;
    admin_level: string;
    department: string;
  };
  statistics: {
    total_students: number;
    active_students: number;
    total_organizations: number;
    active_organizations: number;
    total_org_admins: number;
    active_org_admins: number;
    total_clearances: number;
    current_term?: {
      term_id: number;
      term_name: string;
      academic_year: string;
      semester: string;
      total_clearances: number;
      approved: number;
      pending: number;
      incomplete: number;
    };
  };
  recent_activity: any[];
}

@Injectable({
  providedIn: 'root'
})
export class MISService {
  private apiService = inject(ApiService);

  // ========== Dashboard ==========
  getDashboardStats(): Observable<DashboardStats> {
    return this.apiService.get<DashboardStats>('/mis/dashboard');
  }

  // ========== Students ==========
  getStudents(params?: any): Observable<{ data: Student[], total: number }> {
    return this.apiService.get('/mis/students', params);
  }

  getStudent(id: number): Observable<Student> {
    return this.apiService.get(`/mis/students/${id}`);
  }

  createStudent(data: StudentFormData): Observable<Student> {
    return this.apiService.post('/mis/students', data);
  }

  updateStudent(id: number, data: Partial<StudentFormData>): Observable<Student> {
    return this.apiService.put(`/mis/students/${id}`, data);
  }

  deleteStudent(id: number): Observable<void> {
    return this.apiService.delete(`/mis/students/${id}`);
  }

  // ========== Org Admins ==========
  getOrgAdmins(params?: any): Observable<{ data: OrgAdmin[], total: number }> {
    return this.apiService.get('/mis/org-admins', params);
  }

  getOrgAdmin(id: number): Observable<OrgAdmin> {
    return this.apiService.get(`/mis/org-admins/${id}`);
  }

  createOrgAdmin(data: OrgAdminFormData): Observable<OrgAdmin> {
    return this.apiService.post('/mis/org-admins', data);
  }

  updateOrgAdmin(id: number, data: Partial<OrgAdminFormData>): Observable<OrgAdmin> {
    return this.apiService.put(`/mis/org-admins/${id}`, data);
  }

  deleteOrgAdmin(id: number): Observable<void> {
    return this.apiService.delete(`/mis/org-admins/${id}`);
  }

  // ========== System Admins ==========
  getSysAdmins(params?: any): Observable<{ data: SysAdmin[], total: number }> {
    return this.apiService.get('/mis/sys-admins', params);
  }

  getSysAdmin(id: number): Observable<SysAdmin> {
    return this.apiService.get(`/mis/sys-admins/${id}`);
  }

  createSysAdmin(data: SysAdminFormData): Observable<SysAdmin> {
    return this.apiService.post('/mis/sys-admins', data);
  }

  updateSysAdmin(id: number, data: Partial<SysAdminFormData>): Observable<SysAdmin> {
    return this.apiService.put(`/mis/sys-admins/${id}`, data);
  }

  deleteSysAdmin(id: number): Observable<void> {
    return this.apiService.delete(`/mis/sys-admins/${id}`);
  }

  // ========== Organizations ==========
  getOrganizations(params?: any): Observable<{ data: Organization[], total: number }> {
    return this.apiService.get('/mis/organizations', params);
  }

  getOrganization(id: number): Observable<Organization> {
    return this.apiService.get(`/mis/organizations/${id}`);
  }

  createOrganization(data: OrganizationFormData): Observable<Organization> {
    return this.apiService.post('/mis/organizations', data);
  }

  updateOrganization(id: number, data: Partial<OrganizationFormData>): Observable<Organization> {
    return this.apiService.put(`/mis/organizations/${id}`, data);
  }

  deleteOrganization(id: number): Observable<void> {
    return this.apiService.delete(`/mis/organizations/${id}`);
  }

  // ========== Academic Terms ==========
  getTerms(params?: any): Observable<{ data: AcademicTerm[], total: number }> {
    return this.apiService.get('/mis/terms', params);
  }

  getTerm(id: number): Observable<AcademicTerm> {
    return this.apiService.get(`/mis/terms/${id}`);
  }

  createTerm(data: AcademicTermFormData): Observable<AcademicTerm> {
    return this.apiService.post('/mis/terms', data);
  }

  updateTerm(id: number, data: Partial<AcademicTermFormData>): Observable<AcademicTerm> {
    return this.apiService.put(`/mis/terms/${id}`, data);
  }

  deleteTerm(id: number): Observable<void> {
    return this.apiService.delete(`/mis/terms/${id}`);
  }

  setCurrentTerm(id: number): Observable<AcademicTerm> {
    return this.apiService.post(`/mis/terms/${id}/set-current`, {});
  }

  // ========== Clearances ==========
  getClearances(params?: any): Observable<{ data: Clearance[], total: number }> {
    return this.apiService.get('/mis/clearances', params);
  }

  getClearance(id: number): Observable<Clearance> {
    return this.apiService.get(`/mis/clearances/${id}`);
  }

  getClearanceItems(id: number): Observable<ClearanceItem[]> {
    return this.apiService.get(`/mis/clearances/${id}/items`);
  }

  getClearanceStatsByOrg(params?: any): Observable<OrganizationClearanceStats[]> {
    return this.apiService.get('/mis/clearances/stats-by-organization', params);
  }

  // ========== Audit Logs ==========
  getAuditLogs(params?: any): Observable<{ data: AuditLog[], total: number }> {
    return this.apiService.get('/mis/audit-logs', params);
  }

  getUserAuditLogs(userId: number, params?: any): Observable<{ data: AuditLog[], total: number }> {
    return this.apiService.get(`/mis/audit-logs/user/${userId}`, params);
  }

  getAuditLogStats(): Observable<AuditLogStats> {
    return this.apiService.get('/mis/audit-logs/stats');
  }
}
