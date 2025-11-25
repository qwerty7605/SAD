import { Component, OnInit, inject } from '@angular/core';
import { CommonModule, DatePipe } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { Router, ActivatedRoute } from '@angular/router';
import { ClearanceService } from '../../services/clearance.service';
import { ClearanceItem } from '../../models/clearance.model';

@Component({
  selector: 'app-clearance-list',
  standalone: true,
  imports: [CommonModule, FormsModule, DatePipe],
  templateUrl: './clearance-list.component.html',
  styleUrl: './clearance-list.component.scss'
})
export class ClearanceListComponent implements OnInit {
  private clearanceService = inject(ClearanceService);
  private router = inject(Router);
  private route = inject(ActivatedRoute);

  clearances: ClearanceItem[] = [];
  filteredClearances: ClearanceItem[] = [];
  selectedClearances: number[] = [];

  searchTerm = '';
  statusFilter: 'all' | 'pending' | 'approved' | 'needs_compliance' = 'all';
  isLoading = true;

  ngOnInit() {
    // Check for status query param
    this.route.queryParams.subscribe(params => {
      if (params['status'] && ['all', 'pending', 'approved', 'needs_compliance'].includes(params['status'])) {
        this.statusFilter = params['status'];
      }
    });

    this.loadClearances();
  }

  loadClearances() {
    this.isLoading = true;
    this.clearanceService.getPendingClearances().subscribe({
      next: (data) => {
        this.clearances = data;
        this.applyFilters();
        this.isLoading = false;
      },
      error: (error) => {
        console.error('Error loading clearances:', error);
        this.isLoading = false;
      }
    });
  }

  applyFilters() {
    let filtered = [...this.clearances];

    // Apply status filter
    if (this.statusFilter !== 'all') {
      filtered = filtered.filter(c => c.status === this.statusFilter);
    }

    // Apply search filter
    if (this.searchTerm) {
      const search = this.searchTerm.toLowerCase();
      filtered = filtered.filter(c =>
        c.student_number?.toLowerCase().includes(search) ||
        c.student_name?.toLowerCase().includes(search) ||
        `${c.student_number || ''} ${c.student_name || ''}`.toLowerCase().includes(search)
      );
    }

    this.filteredClearances = filtered;
  }

  onSearchChange() {
    this.applyFilters();
  }

  onStatusFilterChange() {
    this.applyFilters();
  }

  toggleSelection(itemId: number) {
    const index = this.selectedClearances.indexOf(itemId);
    if (index > -1) {
      this.selectedClearances.splice(index, 1);
    } else {
      this.selectedClearances.push(itemId);
    }
  }

  toggleSelectAll(event: any) {
    if (event.target.checked) {
      this.selectedClearances = this.filteredClearances.map(c => c.item_id);
    } else {
      this.selectedClearances = [];
    }
  }

  isSelected(itemId: number): boolean {
    return this.selectedClearances.includes(itemId);
  }

  approveClearance(itemId: number) {
    if (confirm('Are you sure you want to approve this clearance? The student will be marked as cleared for your office.')) {
      this.clearanceService.approveClearance(itemId).subscribe({
        next: () => {
          this.loadClearances();
        },
        error: (error) => {
          alert('Error approving clearance: ' + (error.message || 'Please try again'));
        }
      });
    }
  }

  declineClearance(itemId: number) {
    if (confirm('Mark this clearance as needing compliance? The student will need to visit your office to resolve issues.')) {
      this.clearanceService.rejectClearance(itemId).subscribe({
        next: () => {
          this.loadClearances();
        },
        error: (error) => {
          alert('Error updating clearance: ' + (error.message || 'Please try again'));
        }
      });
    }
  }

  batchApprove() {
    if (this.selectedClearances.length === 0) {
      alert('Please select at least one clearance to approve');
      return;
    }

    if (confirm(`Approve ${this.selectedClearances.length} selected clearance(s)?`)) {
      this.clearanceService.bulkApprove(this.selectedClearances).subscribe({
        next: () => {
          this.selectedClearances = [];
          this.loadClearances();
        },
        error: (error) => {
          alert('Error approving clearances: ' + (error.message || 'Please try again'));
        }
      });
    }
  }

  getStatusClass(status: 'approved' | 'pending' | 'needs_compliance'): string {
    switch (status) {
      case 'approved': return 'status-approved';
      case 'pending': return 'status-pending';
      case 'needs_compliance': return 'status-needs-compliance';
      default: return '';
    }
  }

  getStatusLabel(status: 'approved' | 'pending' | 'needs_compliance'): string {
    switch (status) {
      case 'approved': return '✅ Approved';
      case 'pending': return '⏳ Pending';
      case 'needs_compliance': return '❌ Needs Compliance';
      default: return status;
    }
  }
}
