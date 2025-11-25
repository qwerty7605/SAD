export interface Organization {
  org_id: number;
  org_code: string;
  org_name: string;
  org_type: 'academic' | 'administrative' | 'finance' | 'student_services';
  department?: string;
  is_active: boolean;
  requires_clearance: boolean;
  created_at: string;
  // Additional stats
  admins_count?: number;
  clearance_items_count?: number;
}

export interface OrganizationFormData {
  org_code: string;
  org_name: string;
  org_type: 'academic' | 'administrative' | 'finance' | 'student_services';
  department?: string;
  is_active: boolean;
  requires_clearance: boolean;
}
