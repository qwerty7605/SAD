import { Component, inject, OnInit, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { MISService, DashboardStats } from '../../services/mis.service';

@Component({
  selector: 'app-dashboard',
  standalone: true,
  imports: [CommonModule, RouterModule],
  templateUrl: './dashboard.component.html',
  styleUrl: './dashboard.component.scss'
})
export class DashboardComponent implements OnInit {
  private misService = inject(MISService);

  stats = signal<DashboardStats | null>(null);
  isLoading = signal(true);
  errorMessage = signal('');

  ngOnInit() {
    this.loadDashboard();
  }

  loadDashboard() {
    this.isLoading.set(true);
    this.errorMessage.set('');

    this.misService.getDashboardStats().subscribe({
      next: (data) => {
        this.stats.set(data);
        this.isLoading.set(false);
      },
      error: (error) => {
        this.errorMessage.set(error.message || 'Failed to load dashboard data');
        this.isLoading.set(false);
      }
    });
  }

  getApprovalPercentage(): number {
    const currentTerm = this.stats()?.statistics?.current_term;
    if (!currentTerm || currentTerm.total_clearances === 0) return 0;
    return Math.round((currentTerm.approved / currentTerm.total_clearances) * 100);
  }
}
