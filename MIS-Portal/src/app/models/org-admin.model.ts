export interface OrgAdmin {
  admin_id: number;
  org_id: number;
  position: string;
  full_name: string;
  assigned_date: string;
  removed_date?: string | null;
  is_active: boolean;
  // Relations
  organization?: {
    org_id: number;
    org_name: string;
    org_code: string;
  };
}

export interface OrgAdminFormData {
  org_id: number;
  full_name: string;
  position: string;
  is_active: boolean;
}
