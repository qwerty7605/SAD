import { Component, OnInit, signal, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { MISService } from '../../services/mis.service';
import { AcademicTerm, AcademicTermFormData } from '../../models/term.model';

@Component({
  selector: 'app-terms',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './terms.component.html',
  styleUrl: './terms.component.scss'
})
export class TermsComponent implements OnInit {
  private misService = inject(MISService);

  // State
  terms = signal<AcademicTerm[]>([]);
  isLoading = signal(false);
  errorMessage = signal('');
  total = signal(0);

  // Filters
  searchQuery = signal('');
  semesterFilter = signal<'all' | 'first' | 'second' | 'summer'>('all');
  isCurrentFilter = signal<'all' | 'yes' | 'no'>('all');

  // Pagination
  currentPage = signal(1);
  perPage = signal(15);
  totalPages = signal(0);

  // Modal state
  showModal = signal(false);
  modalMode = signal<'create' | 'edit'>('create');
  selectedTerm = signal<AcademicTerm | null>(null);

  // Form data
  formData = signal<AcademicTermFormData>({
    term_name: '',
    academic_year: '',
    semester: 'first',
    start_date: '',
    end_date: '',
    enrollment_start: '',
    enrollment_end: '',
    is_current: false,
    clearance_deadline: null
  });

  // Delete confirmation
  showDeleteModal = signal(false);
  termToDelete = signal<AcademicTerm | null>(null);

  // Set Current confirmation
  showSetCurrentModal = signal(false);
  termToSetCurrent = signal<AcademicTerm | null>(null);

  ngOnInit() {
    this.loadTerms();
  }

  loadTerms() {
    this.isLoading.set(true);
    this.errorMessage.set('');

    const params: any = {
      page: this.currentPage(),
      per_page: this.perPage()
    };

    if (this.searchQuery()) {
      params.search = this.searchQuery();
    }

    if (this.semesterFilter() !== 'all') {
      params.semester = this.semesterFilter();
    }

    if (this.isCurrentFilter() !== 'all') {
      params.is_current = this.isCurrentFilter() === 'yes' ? 1 : 0;
    }

    this.misService.getTerms(params).subscribe({
      next: (response) => {
        this.terms.set(response.data);
        this.total.set(response.total);
        this.totalPages.set(Math.ceil(response.total / this.perPage()));
        this.isLoading.set(false);
      },
      error: (error) => {
        this.errorMessage.set(error.message || 'Failed to load academic terms');
        this.isLoading.set(false);
      }
    });
  }

  onSearch() {
    this.currentPage.set(1);
    this.loadTerms();
  }

  onFilterChange() {
    this.currentPage.set(1);
    this.loadTerms();
  }

  nextPage() {
    if (this.currentPage() < this.totalPages()) {
      this.currentPage.update(page => page + 1);
      this.loadTerms();
    }
  }

  previousPage() {
    if (this.currentPage() > 1) {
      this.currentPage.update(page => page - 1);
      this.loadTerms();
    }
  }

  openCreateModal() {
    this.modalMode.set('create');
    this.formData.set({
      term_name: '',
      academic_year: '',
      semester: 'first',
      start_date: '',
      end_date: '',
      enrollment_start: '',
      enrollment_end: '',
      is_current: false,
      clearance_deadline: null
    });
    this.showModal.set(true);
  }

  openEditModal(term: AcademicTerm) {
    this.modalMode.set('edit');
    this.selectedTerm.set(term);
    this.formData.set({
      term_name: term.term_name,
      academic_year: term.academic_year,
      semester: term.semester,
      start_date: term.start_date,
      end_date: term.end_date,
      enrollment_start: term.enrollment_start,
      enrollment_end: term.enrollment_end,
      is_current: term.is_current,
      clearance_deadline: term.clearance_deadline
    });
    this.showModal.set(true);
  }

  closeModal() {
    this.showModal.set(false);
    this.selectedTerm.set(null);
  }

  onSubmit() {
    const data = this.formData();

    // Validation: End date must be after start date
    if (data.start_date && data.end_date) {
      const startDate = new Date(data.start_date);
      const endDate = new Date(data.end_date);

      if (endDate <= startDate) {
        this.errorMessage.set('End date must be after start date');
        return;
      }
    }

    // Validation: Enrollment end must be after enrollment start
    if (data.enrollment_start && data.enrollment_end) {
      const enrollStart = new Date(data.enrollment_start);
      const enrollEnd = new Date(data.enrollment_end);

      if (enrollEnd <= enrollStart) {
        this.errorMessage.set('Enrollment end date must be after enrollment start date');
        return;
      }
    }

    if (this.modalMode() === 'create') {
      this.misService.createTerm(data).subscribe({
        next: () => {
          this.closeModal();
          this.loadTerms();
          this.errorMessage.set('');
        },
        error: (error) => {
          this.errorMessage.set(error.message || 'Failed to create academic term');
        }
      });
    } else {
      const termId = this.selectedTerm()?.term_id;
      if (termId) {
        this.misService.updateTerm(termId, data).subscribe({
          next: () => {
            this.closeModal();
            this.loadTerms();
            this.errorMessage.set('');
          },
          error: (error) => {
            this.errorMessage.set(error.message || 'Failed to update academic term');
          }
        });
      }
    }
  }

  openDeleteModal(term: AcademicTerm) {
    this.termToDelete.set(term);
    this.showDeleteModal.set(true);
  }

  closeDeleteModal() {
    this.showDeleteModal.set(false);
    this.termToDelete.set(null);
  }

  confirmDelete() {
    const term = this.termToDelete();
    if (term) {
      this.misService.deleteTerm(term.term_id).subscribe({
        next: () => {
          this.closeDeleteModal();
          this.loadTerms();
        },
        error: (error) => {
          this.errorMessage.set(error.message || 'Failed to delete academic term');
          this.closeDeleteModal();
        }
      });
    }
  }

  openSetCurrentModal(term: AcademicTerm) {
    // Warn if trying to set a past term as current
    const endDate = new Date(term.end_date);
    const today = new Date();

    if (endDate < today) {
      const confirmPastTerm = confirm('Warning: This term has already ended. Are you sure you want to set it as the current term?');
      if (!confirmPastTerm) {
        return;
      }
    }

    this.termToSetCurrent.set(term);
    this.showSetCurrentModal.set(true);
  }

  closeSetCurrentModal() {
    this.showSetCurrentModal.set(false);
    this.termToSetCurrent.set(null);
  }

  confirmSetCurrent() {
    const term = this.termToSetCurrent();
    if (term) {
      this.misService.setCurrentTerm(term.term_id).subscribe({
        next: () => {
          this.closeSetCurrentModal();
          this.loadTerms();
          this.errorMessage.set('');
        },
        error: (error) => {
          this.errorMessage.set(error.message || 'Failed to set current term');
          this.closeSetCurrentModal();
        }
      });
    }
  }

  canDelete(term: AcademicTerm): boolean {
    return !term.is_current;
  }

  formatDate(date: string): string {
    return new Date(date).toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric'
    });
  }

  getDuration(term: AcademicTerm): string {
    return `${this.formatDate(term.start_date)} - ${this.formatDate(term.end_date)}`;
  }

  // Helper for template
  Math = Math;
}
