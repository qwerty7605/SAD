export interface SysAdmin {
  sys_admin_id: number;
  user_id: number;
  full_name: string;
  admin_level: 'super_admin' | 'mis_staff';
  department?: string;
  assigned_date: string;
  created_at: string;
  updated_at: string;
  // Relations
  user?: {
    user_id: number;
    username: string;
    email: string;
    user_type: string;
    is_active: boolean;
  };
}

export interface SysAdminFormData {
  full_name: string;
  admin_level: 'super_admin' | 'mis_staff';
  department?: string;
}
