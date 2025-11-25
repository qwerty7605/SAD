export interface AcademicTerm {
  term_id: number;
  academic_year: string;
  semester: 'first' | 'second' | 'summer';
  term_name: string;
  start_date: string;
  end_date: string;
  enrollment_start: string;
  enrollment_end: string;
  is_current: boolean;
  clearance_deadline: string | null;
  created_at: string;
  // Additional stats
  clearances_count?: number;
}

export interface AcademicTermFormData {
  term_name: string;
  academic_year: string;
  semester: 'first' | 'second' | 'summer';
  start_date: string;
  end_date: string;
  enrollment_start: string;
  enrollment_end: string;
  is_current: boolean;
  clearance_deadline: string | null;
}
