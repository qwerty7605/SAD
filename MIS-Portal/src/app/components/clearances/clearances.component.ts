import { Component, OnInit, signal, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { MISService } from '../../services/mis.service';
import { Clearance, ClearanceItem, OrganizationClearanceStats } from '../../models/clearance.model';

@Component({
  selector: 'app-clearances',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './clearances.component.html',
  styleUrl: './clearances.component.scss'
})
export class ClearancesComponent implements OnInit {
  private misService = inject(MISService);

  // State
  clearances = signal<Clearance[]>([]);
  isLoading = signal(false);
  errorMessage = signal('');
  total = signal(0);

  // Statistics
  orgStats = signal<OrganizationClearanceStats[]>([]);
  isLoadingStats = signal(false);
  showStats = signal(true);

  // Terms for dropdown
  terms = signal<any[]>([]);
  isLoadingTerms = signal(false);

  // Filters
  searchQuery = signal('');
  overallStatusFilter = signal<'all' | 'approved' | 'pending' | 'incomplete'>('all');
  termFilter = signal<number | 'all'>('all');
  programFilter = signal('');

  // Pagination
  currentPage = signal(1);
  perPage = signal(15);
  totalPages = signal(0);

  // Details Modal
  showDetailsModal = signal(false);
  selectedClearance = signal<Clearance | null>(null);
  clearanceItems = signal<ClearanceItem[]>([]);
  isLoadingItems = signal(false);

  ngOnInit() {
    this.loadTerms();
    this.loadClearances();
    this.loadOrgStats();
  }

  loadTerms() {
    this.isLoadingTerms.set(true);
    this.misService.getTerms({ per_page: 100 }).subscribe({
      next: (response) => {
        this.terms.set(response.data);
        this.isLoadingTerms.set(false);
      },
      error: (error) => {
        console.error('Failed to load terms:', error);
        this.isLoadingTerms.set(false);
      }
    });
  }

  loadClearances() {
    this.isLoading.set(true);
    this.errorMessage.set('');

    const params: any = {
      page: this.currentPage(),
      per_page: this.perPage()
    };

    if (this.searchQuery()) {
      params.search = this.searchQuery();
    }

    if (this.overallStatusFilter() !== 'all') {
      params.overall_status = this.overallStatusFilter();
    }

    if (this.termFilter() !== 'all') {
      params.term_id = this.termFilter();
    }

    if (this.programFilter()) {
      params.program = this.programFilter();
    }

    this.misService.getClearances(params).subscribe({
      next: (response) => {
        this.clearances.set(response.data);
        this.total.set(response.total);
        this.totalPages.set(Math.ceil(response.total / this.perPage()));
        this.isLoading.set(false);
      },
      error: (error) => {
        this.errorMessage.set(error.message || 'Failed to load clearances');
        this.isLoading.set(false);
      }
    });
  }

  loadOrgStats() {
    this.isLoadingStats.set(true);
    this.misService.getClearanceStatsByOrg().subscribe({
      next: (stats) => {
        this.orgStats.set(stats);
        this.isLoadingStats.set(false);
      },
      error: (error) => {
        console.error('Failed to load organization statistics:', error);
        this.isLoadingStats.set(false);
      }
    });
  }

  onSearch() {
    this.currentPage.set(1);
    this.loadClearances();
  }

  onFilterChange() {
    this.currentPage.set(1);
    this.loadClearances();
  }

  nextPage() {
    if (this.currentPage() < this.totalPages()) {
      this.currentPage.update(page => page + 1);
      this.loadClearances();
    }
  }

  previousPage() {
    if (this.currentPage() > 1) {
      this.currentPage.update(page => page - 1);
      this.loadClearances();
    }
  }

  toggleStats() {
    this.showStats.update(val => !val);
  }

  openDetailsModal(clearance: Clearance) {
    this.selectedClearance.set(clearance);
    this.showDetailsModal.set(true);
    this.loadClearanceItems(clearance.clearance_id);
  }

  closeDetailsModal() {
    this.showDetailsModal.set(false);
    this.selectedClearance.set(null);
    this.clearanceItems.set([]);
  }

  loadClearanceItems(clearanceId: number) {
    this.isLoadingItems.set(true);
    this.misService.getClearanceItems(clearanceId).subscribe({
      next: (items) => {
        this.clearanceItems.set(items);
        this.isLoadingItems.set(false);
      },
      error: (error) => {
        console.error('Failed to load clearance items:', error);
        this.isLoadingItems.set(false);
      }
    });
  }

  getStatusClass(status: string): string {
    return status.toLowerCase();
  }

  getProgressText(clearance: Clearance): string {
    const approved = clearance.approved_items || 0;
    const total = clearance.total_items || 0;
    return `${approved}/${total} approved`;
  }

  formatDate(dateString?: string): string {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric'
    });
  }

  formatDateTime(dateString?: string): string {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    });
  }

  getApprovalRate(stat: OrganizationClearanceStats): string {
    return `${stat.approval_rate.toFixed(1)}%`;
  }

  getStudentFullName(clearance: Clearance): string {
    if (!clearance.student) return 'N/A';
    return `${clearance.student.first_name} ${clearance.student.last_name}`;
  }

  getApproverFullName(item: ClearanceItem): string {
    if (!item.approver) return 'N/A';
    return `${item.approver.first_name} ${item.approver.last_name}`;
  }

  // Helper for template
  Math = Math;
}
