import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { ClearanceService } from '../../services/clearance.service';

@Component({
  selector: 'app-clearance',
  standalone: true,
  imports: [CommonModule, RouterModule],
  templateUrl: './clearance.html',
  styleUrls: ['./clearance.scss'],
})
export class ClearanceComponent implements OnInit {
  clearances: any[] = [];
  menuOpen = false;
  user: any = {};

  constructor(private clearanceService: ClearanceService) {}

  ngOnInit(): void {
    const user = JSON.parse(localStorage.getItem('user') || '{}');
    this.user = user;

    // Get student clearances
    this.clearanceService.getStudentClearances().subscribe({
      next: (data) => {
        this.clearances = data;
        console.log('Clearances loaded:', this.clearances);
      },
      error: (err) => {
        console.error('Error fetching clearances:', err);
      }
    });
  }

  // Get count methods
  getApprovedCount(): number {
    return this.clearances.filter((c) => c.status === 'Approved').length;
  }

  getPendingCount(): number {
    return this.clearances.filter((c) => c.status === 'Pending').length;
  }

  getNeedsComplianceCount(): number {
    return this.clearances.filter((c) => c.status === 'Needs Compliance' || c.status === 'Needs compliance').length;
  }

  // Overall status methods
  getOverallStatusText(): string {
    const total = this.clearances.length;
    const approved = this.getApprovedCount();
    const needsCompliance = this.getNeedsComplianceCount();

    if (total === 0) return 'No Clearance';
    if (approved === total) return 'All Cleared';
    if (needsCompliance > 0) return 'Needs Compliance';
    return 'In Progress';
  }

  getOverallStatusClass(): string {
    const status = this.getOverallStatusText();
    if (status === 'All Cleared') return 'status-approved';
    if (status === 'Needs Compliance') return 'status-needs-compliance';
    return 'status-pending';
  }

  getOverallStatusIcon(): string {
    const status = this.getOverallStatusText();
    if (status === 'All Cleared') return 'bi-check-circle-fill';
    if (status === 'Needs Compliance') return 'bi-exclamation-circle-fill';
    return 'bi-hourglass-split';
  }

  // Status badge methods
  getStatusBadgeClass(status: string): string {
    const normalized = status.toLowerCase();
    if (normalized === 'approved') return 'badge-approved';
    if (normalized === 'pending') return 'badge-pending';
    if (normalized.includes('compliance')) return 'badge-needs-compliance';
    return '';
  }

  getStatusIcon(status: string): string {
    const normalized = status.toLowerCase();
    if (normalized === 'approved') return 'bi-check-circle-fill';
    if (normalized === 'pending') return 'bi-hourglass-split';
    if (normalized.includes('compliance')) return 'bi-exclamation-circle-fill';
    return 'bi-question-circle';
  }

  // Row styling
  getRowClass(status: string): string {
    const normalized = status.toLowerCase();
    if (normalized === 'approved') return 'row-approved';
    if (normalized === 'pending') return 'row-pending';
    if (normalized.includes('compliance')) return 'row-needs-compliance';
    return '';
  }

  // Date formatting
  formatDate(dateString: string | null): string {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
      month: 'short',
      day: '2-digit',
      year: 'numeric'
    });
  }
}
