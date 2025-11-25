export interface Student {
  student_id: number;
  student_number: string;
  first_name: string;
  middle_name?: string;
  last_name: string;
  course: string; // Changed from 'program'
  year_level: number;
  section?: string;
  contact_number?: string;
  date_enrolled?: string;
  enrollment_status: 'enrolled' | 'inactive' | 'graduated' | 'withdrawn'; // Changed from is_active
  student_type: 'regular' | 'irregular';
  // Relations
  user?: {
    user_id: number;
    username: string;
    email: string;
    user_type: string;
    is_active: boolean;
  };
  // Computed from user
  username?: string;
  email?: string;
}

export interface StudentFormData {
  student_number: string;
  first_name: string;
  middle_name?: string;
  last_name: string;
  course: string; // Changed from 'program'
  year_level: number;
  section?: string;
  contact_number?: string;
  enrollment_status: 'enrolled' | 'inactive' | 'graduated' | 'withdrawn'; // Changed from is_active
  student_type: 'regular' | 'irregular';
  // User account fields (for create/update via backend)
  username?: string;
  email?: string;
  password?: string;
}
