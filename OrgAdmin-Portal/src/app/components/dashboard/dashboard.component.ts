import { Component, OnInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Router } from '@angular/router';
import { ClearanceService } from '../../services/clearance.service';
import { AuthService } from '../../services/auth.service';

@Component({
  selector: 'app-dashboard',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './dashboard.component.html',
  styleUrl: './dashboard.component.scss'
})
export class DashboardComponent implements OnInit {
  private clearanceService = inject(ClearanceService);
  private authService = inject(AuthService);
  private router = inject(Router);

  currentUser: any;
  statistics = {
    total: 0,
    pending: 0,
    approved: 0,
    needs_compliance: 0
  };
  isLoading = true;

  ngOnInit() {
    this.currentUser = this.authService.getUser();
    this.loadStatistics();
  }

  loadStatistics() {
    this.isLoading = true;
    this.clearanceService.getStatistics().subscribe({
      next: (stats) => {
        this.statistics = stats;
        this.isLoading = false;
      },
      error: (error) => {
        console.error('Error loading statistics:', error);
        this.isLoading = false;
      }
    });
  }

  viewPendingClearances() {
    this.router.navigate(['/clearances'], { queryParams: { status: 'pending' } });
  }

  viewApprovedClearances() {
    this.router.navigate(['/clearances'], { queryParams: { status: 'approved' } });
  }

  viewNeedsComplianceClearances() {
    this.router.navigate(['/clearances'], { queryParams: { status: 'needs_compliance' } });
  }

  viewAllClearances() {
    this.router.navigate(['/clearances']);
  }
}
