/**
 * Clearance Model - Represents a clearance record
 */

export interface Clearance {
  id: number;
  student_id: number;
  organization_id: number;
  status: ClearanceStatus;
  remarks?: string;
  submitted_at?: string;
  reviewed_at?: string;
  reviewer_id?: number;
  documents?: ClearanceDocument[];
  created_at: string;
  updated_at: string;
}

export enum ClearanceStatus {
  PENDING = 'pending',
  SUBMITTED = 'submitted',
  APPROVED = 'approved',
  REJECTED = 'rejected',
  CONDITIONALLY_APPROVED = 'conditionally_approved'
}

export interface ClearanceDocument {
  id: number;
  clearance_id: number;
  file_name: string;
  file_path: string;
  file_type: string;
  uploaded_at: string;
}

export interface ClearanceItem {
  id: number;
  student_id: number;
  organization_id: number;
  organization_name?: string;
  status: ClearanceStatus;
  remarks?: string;
  submitted_at?: string;
  reviewed_at?: string;
}
