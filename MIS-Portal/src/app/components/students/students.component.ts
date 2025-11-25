import { Component, OnInit, signal, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { MISService } from '../../services/mis.service';
import { Student, StudentFormData } from '../../models/student.model';

@Component({
  selector: 'app-students',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './students.component.html',
  styleUrl: './students.component.scss'
})
export class StudentsComponent implements OnInit {
  private misService = inject(MISService);

  // State
  students = signal<Student[]>([]);
  isLoading = signal(false);
  errorMessage = signal('');
  total = signal(0);

  // Filters
  searchQuery = signal('');
  statusFilter = signal<'all' | 'enrolled' | 'inactive' | 'graduated' | 'withdrawn'>('all');
  courseFilter = signal('');
  yearLevelFilter = signal<number | ''>('');
  studentTypeFilter = signal<'all' | 'regular' | 'irregular'>('all');

  // Pagination
  currentPage = signal(1);
  perPage = signal(15);
  totalPages = signal(0);

  // Modal state
  showModal = signal(false);
  modalMode = signal<'create' | 'edit'>('create');
  selectedStudent = signal<Student | null>(null);

  // Form data
  formData = signal<StudentFormData>({
    student_number: '',
    first_name: '',
    middle_name: '',
    last_name: '',
    course: '',
    year_level: 1,
    section: '',
    contact_number: '',
    enrollment_status: 'enrolled',
    student_type: 'regular',
    username: '',
    email: '',
    password: ''
  });

  // Delete confirmation
  showDeleteModal = signal(false);
  studentToDelete = signal<Student | null>(null);

  ngOnInit() {
    this.loadStudents();
  }

  loadStudents() {
    this.isLoading.set(true);
    this.errorMessage.set('');

    const params: any = {
      page: this.currentPage(),
      per_page: this.perPage()
    };

    if (this.searchQuery()) {
      params.search = this.searchQuery();
    }

    if (this.statusFilter() !== 'all') {
      params.enrollment_status = this.statusFilter();
    }

    if (this.courseFilter()) {
      params.course = this.courseFilter();
    }

    if (this.yearLevelFilter()) {
      params.year_level = this.yearLevelFilter();
    }

    if (this.studentTypeFilter() !== 'all') {
      params.student_type = this.studentTypeFilter();
    }

    this.misService.getStudents(params).subscribe({
      next: (response) => {
        this.students.set(response.data);
        this.total.set(response.total);
        this.totalPages.set(Math.ceil(response.total / this.perPage()));
        this.isLoading.set(false);
      },
      error: (error) => {
        this.errorMessage.set(error.message || 'Failed to load students');
        this.isLoading.set(false);
      }
    });
  }

  onSearch() {
    this.currentPage.set(1);
    this.loadStudents();
  }

  onFilterChange() {
    this.currentPage.set(1);
    this.loadStudents();
  }

  nextPage() {
    if (this.currentPage() < this.totalPages()) {
      this.currentPage.update(page => page + 1);
      this.loadStudents();
    }
  }

  previousPage() {
    if (this.currentPage() > 1) {
      this.currentPage.update(page => page - 1);
      this.loadStudents();
    }
  }

  openCreateModal() {
    this.modalMode.set('create');
    this.formData.set({
      student_number: '',
      first_name: '',
      middle_name: '',
      last_name: '',
      course: '',
      year_level: 1,
      section: '',
      contact_number: '',
      enrollment_status: 'enrolled',
      student_type: 'regular',
      username: '',
      email: '',
      password: ''
    });
    this.showModal.set(true);
  }

  openEditModal(student: Student) {
    this.modalMode.set('edit');
    this.selectedStudent.set(student);
    this.formData.set({
      student_number: student.student_number,
      first_name: student.first_name,
      middle_name: student.middle_name,
      last_name: student.last_name,
      course: student.course,
      year_level: student.year_level,
      section: student.section,
      contact_number: student.contact_number,
      enrollment_status: student.enrollment_status,
      student_type: student.student_type,
      username: student.username || student.user?.username || '',
      email: student.email || student.user?.email || '',
      password: ''
    });
    this.showModal.set(true);
  }

  closeModal() {
    this.showModal.set(false);
    this.selectedStudent.set(null);
  }

  onSubmit() {
    const data = this.formData();

    if (this.modalMode() === 'create') {
      this.misService.createStudent(data).subscribe({
        next: () => {
          this.closeModal();
          this.loadStudents();
        },
        error: (error) => {
          this.errorMessage.set(error.message || 'Failed to create student');
        }
      });
    } else {
      const studentId = this.selectedStudent()?.student_id;
      if (studentId) {
        // Remove password if empty for updates
        const updateData = { ...data };
        if (!updateData.password) {
          delete updateData.password;
        }

        this.misService.updateStudent(studentId, updateData).subscribe({
          next: () => {
            this.closeModal();
            this.loadStudents();
          },
          error: (error) => {
            this.errorMessage.set(error.message || 'Failed to update student');
          }
        });
      }
    }
  }

  openDeleteModal(student: Student) {
    this.studentToDelete.set(student);
    this.showDeleteModal.set(true);
  }

  closeDeleteModal() {
    this.showDeleteModal.set(false);
    this.studentToDelete.set(null);
  }

  confirmDelete() {
    const student = this.studentToDelete();
    if (student) {
      this.misService.deleteStudent(student.student_id).subscribe({
        next: () => {
          this.closeDeleteModal();
          this.loadStudents();
        },
        error: (error) => {
          this.errorMessage.set(error.message || 'Failed to delete student');
          this.closeDeleteModal();
        }
      });
    }
  }

  getFullName(student: Student): string {
    const parts = [
      student.first_name,
      student.middle_name,
      student.last_name
    ].filter(Boolean);
    return parts.join(' ');
  }

  // Helper for template
  Math = Math;
}
