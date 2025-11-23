/**
 * Organization Model - Represents an organization/department
 */

export interface Organization {
  id: number;
  name: string;
  code: string;
  type: OrganizationType;
  description?: string;
  requirements?: string;
  is_active: boolean;
  created_at: string;
  updated_at: string;
}

export enum OrganizationType {
  DEPARTMENT = 'department',
  OFFICE = 'office',
  LIBRARY = 'library',
  STUDENT_ORG = 'student_org',
  OTHER = 'other'
}

export interface OrganizationRequirement {
  id: number;
  organization_id: number;
  title: string;
  description: string;
  is_required: boolean;
  created_at: string;
  updated_at: string;
}
