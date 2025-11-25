/**
 * Clearance Models - Based on ERD Structure
 * 
 * ERD Entities:
 * - STUDENT_CLEARANCES: Main clearance record per student per term
 * - CLEARANCE_ITEMS: Individual organization clearance status
 */

/**
 * Main Student Clearance Record (from STUDENT_CLEARANCES table)
 */
export interface StudentClearance {
  clearance_id: number;
  student_id: number;
  term_id: number;
  overall_status: 'approved' | 'pending' | 'incomplete';
  created_at: string;
  last_updated: string;
  approved_date?: string;
  is_locked: boolean;
  
  // Related data (from joins)
  student?: {
    student_id: number;
    student_number: string;
    first_name: string;
    middle_name?: string;
    last_name: string;
    course?: string;
    year_level?: number;
    section?: string;
  };
  term?: {
    term_id: number;
    term_name: string;
    academic_year: string;
    semester: 'first' | 'second' | 'summer';
  };
  clearance_items?: ClearanceItem[];
}

/**
 * Clearance Item (from CLEARANCE_ITEMS table)
 * Represents individual organization clearance status
 */
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
  
  // Related data (from joins)
  organization?: {
    org_id: number;
    org_code: string;
    org_name: string;
    org_type?: string;
  };
  approver?: {
    admin_id: number;
    full_name: string;
    position?: string;
  };
  // Student info for display
  student_number?: string;
  student_name?: string;
}

/**
 * Clearance Statistics for Dashboard
 */
export interface ClearanceStatistics {
  total: number;
  pending: number;
  approved: number;
  needs_compliance: number;
  organization?: {
    org_id: number;
    org_name: string;
    org_code: string;
  };
}
