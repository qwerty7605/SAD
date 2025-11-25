export interface AuditLog {
  log_id: number;
  user_id?: number;
  action_type: 'create' | 'update' | 'delete' | 'login' | 'logout';
  table_name?: string;
  record_id?: number;
  old_value?: string;
  new_value?: string;
  ip_address?: string;
  user_agent?: string;
  created_at: string;
  // Relations
  user?: {
    user_id: number;
    username: string;
    email: string;
    user_type: string;
  };
}

export interface AuditLogStats {
  total_logs: number;
  today: number;
  this_week: number;
  this_month: number;
  by_action: {
    action_type: string;
    count: number;
  }[];
  by_table: {
    table_name: string;
    count: number;
  }[];
}
