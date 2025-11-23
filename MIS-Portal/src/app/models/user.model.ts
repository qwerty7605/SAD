/**
 * User Model - Represents a user in the system
 */

export interface User {
  id: number;
  name: string;
  email: string;
  role: UserRole;
  student_id?: string;
  organization_id?: number;
  created_at: string;
  updated_at: string;
}

export enum UserRole {
  STUDENT = 'student',
  ORG_ADMIN = 'org_admin',
  MIS_ADMIN = 'mis_admin'
}

export interface Student extends User {
  student_id: string;
  program?: string;
  year_level?: number;
  section?: string;
}
