export interface Clearance {
  clearance_id: number;
  student_id: number;
  term_id: number;
  overall_status: 'approved' | 'pending' | 'incomplete';
  created_at: string;
  last_updated: string;
  approved_date?: string;
  is_locked: boolean;
  // Relations
  student?: {
    student_id: number;
    student_number: string;
    first_name: string;
    last_name: string;
    program: string;
    year_level: number;
  };
  term?: {
    term_id: number;
    term_name: string;
    term_code: string;
  };
  // Stats
  total_items?: number;
  approved_items?: number;
  pending_items?: number;
  needs_compliance_items?: number;
}

export interface ClearanceItem {
  item_id: number;
  clearance_id: number;
  org_id: number;
  status: 'approved' | 'pending' | 'needs_compliance';
  approved_by?: number;
  approved_date?: string;
  is_auto_approved: boolean;
  created_at: string;
  status_updated: string;
  // Relations
  organization?: {
    org_id: number;
    org_name: string;
    org_code: string;
  };
  approver?: {
    admin_id: number;
    first_name: string;
    last_name: string;
  };
}

export interface OrganizationClearanceStats {
  org_id: number;
  org_name: string;
  org_code: string;
  total_clearances: number;
  approved: number;
  pending: number;
  needs_compliance: number;
  approval_rate: number;
}
