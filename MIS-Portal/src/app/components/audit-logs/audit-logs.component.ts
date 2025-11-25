import { Component, OnInit, signal, inject, computed } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { MISService } from '../../services/mis.service';
import { AuditLog, AuditLogStats } from '../../models/audit-log.model';

@Component({
  selector: 'app-audit-logs',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './audit-logs.component.html',
  styleUrl: './audit-logs.component.scss'
})
export class AuditLogsComponent implements OnInit {
  private misService = inject(MISService);

  // State
  logs = signal<AuditLog[]>([]);
  stats = signal<AuditLogStats | null>(null);
  isLoading = signal(false);
  isLoadingStats = signal(false);
  errorMessage = signal('');
  total = signal(0);

  // Filters
  searchQuery = signal('');
  actionTypeFilter = signal('');
  tableNameFilter = signal('');
  userIdFilter = signal<number | ''>('');
  startDate = signal('');
  endDate = signal('');

  // Pagination
  currentPage = signal(1);
  perPage = signal(25);
  totalPages = signal(0);

  // Modal state
  showDetailsModal = signal(false);
  selectedLog = signal<AuditLog | null>(null);

  // Statistics section toggle
  showStats = signal(true);

  // Available filter options
  availableActionTypes = [
    'create',
    'update',
    'delete',
    'login',
    'logout'
  ];

  availableTableNames = [
    'students',
    'organizations',
    'clearances',
    'users',
    'organization_admins',
    'system_admins',
    'terms',
    'clearance_items'
  ];

  ngOnInit() {
    this.loadLogs();
    this.loadStats();
  }

  loadLogs() {
    this.isLoading.set(true);
    this.errorMessage.set('');

    const params: any = {
      page: this.currentPage(),
      per_page: this.perPage()
    };

    if (this.searchQuery()) {
      params.search = this.searchQuery();
    }

    if (this.actionTypeFilter()) {
      params.action_type = this.actionTypeFilter();
    }

    if (this.tableNameFilter()) {
      params.table_name = this.tableNameFilter();
    }

    if (this.userIdFilter()) {
      params.user_id = this.userIdFilter();
    }

    if (this.startDate()) {
      params.start_date = this.startDate();
    }

    if (this.endDate()) {
      params.end_date = this.endDate();
    }

    this.misService.getAuditLogs(params).subscribe({
      next: (response) => {
        this.logs.set(response.data);
        this.total.set(response.total);
        this.totalPages.set(Math.ceil(response.total / this.perPage()));
        this.isLoading.set(false);
      },
      error: (error) => {
        this.errorMessage.set(error.message || 'Failed to load audit logs');
        this.isLoading.set(false);
      }
    });
  }

  loadStats() {
    this.isLoadingStats.set(true);
    this.misService.getAuditLogStats().subscribe({
      next: (stats) => {
        this.stats.set(stats);
        this.isLoadingStats.set(false);
      },
      error: (error) => {
        console.error('Failed to load stats:', error);
        this.isLoadingStats.set(false);
      }
    });
  }

  onSearch() {
    this.currentPage.set(1);
    this.loadLogs();
  }

  onFilterChange() {
    this.currentPage.set(1);
    this.loadLogs();
  }

  setQuickDateRange(range: 'today' | 'week' | 'month') {
    const now = new Date();
    const today = now.toISOString().split('T')[0];

    this.endDate.set(today);

    switch (range) {
      case 'today':
        this.startDate.set(today);
        break;
      case 'week':
        const weekAgo = new Date(now);
        weekAgo.setDate(weekAgo.getDate() - 7);
        this.startDate.set(weekAgo.toISOString().split('T')[0]);
        break;
      case 'month':
        const monthAgo = new Date(now);
        monthAgo.setMonth(monthAgo.getMonth() - 1);
        this.startDate.set(monthAgo.toISOString().split('T')[0]);
        break;
    }

    this.onFilterChange();
  }

  clearDateRange() {
    this.startDate.set('');
    this.endDate.set('');
    this.onFilterChange();
  }

  nextPage() {
    if (this.currentPage() < this.totalPages()) {
      this.currentPage.update(page => page + 1);
      this.loadLogs();
    }
  }

  previousPage() {
    if (this.currentPage() > 1) {
      this.currentPage.update(page => page - 1);
      this.loadLogs();
    }
  }

  openDetailsModal(log: AuditLog) {
    this.selectedLog.set(log);
    this.showDetailsModal.set(true);
  }

  closeDetailsModal() {
    this.showDetailsModal.set(false);
    this.selectedLog.set(null);
  }

  filterByUser(userId: number) {
    this.userIdFilter.set(userId);
    this.onFilterChange();
  }

  clearUserFilter() {
    this.userIdFilter.set('');
    this.onFilterChange();
  }

  toggleStats() {
    this.showStats.update(show => !show);
  }

  getActionClass(actionType: string): string {
    const actionLower = actionType.toLowerCase();
    if (actionLower === 'create') return 'action-create';
    if (actionLower === 'update') return 'action-update';
    if (actionLower === 'delete') return 'action-delete';
    if (actionLower === 'login') return 'action-login';
    if (actionLower === 'logout') return 'action-logout';
    return 'action-default';
  }

  formatValue(value: string | undefined): string {
    if (!value) return 'N/A';

    try {
      const parsed = JSON.parse(value);
      return JSON.stringify(parsed, null, 2);
    } catch (e) {
      return value;
    }
  }

  parseValuePairs(value: string | undefined): { key: string; value: any }[] {
    if (!value) return [];

    try {
      const parsed = JSON.parse(value);
      if (typeof parsed === 'object') {
        return Object.entries(parsed).map(([key, val]) => ({
          key,
          value: typeof val === 'object' ? JSON.stringify(val, null, 2) : val
        }));
      }
    } catch (e) {
      // Not JSON, return as single item
      return [{ key: 'Value', value }];
    }

    return [];
  }

  exportToCSV() {
    // Simple CSV export functionality
    const headers = ['Timestamp', 'User', 'Action Type', 'Table Name', 'Record ID', 'IP Address'];
    const rows = this.logs().map(log => [
      log.created_at,
      log.user?.email || 'System',
      log.action_type,
      log.table_name || 'N/A',
      log.record_id || 'N/A',
      log.ip_address || 'N/A'
    ]);

    const csvContent = [
      headers.join(','),
      ...rows.map(row => row.map(cell => `"${cell}"`).join(','))
    ].join('\n');

    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `audit-logs-${new Date().toISOString().split('T')[0]}.csv`;
    a.click();
    window.URL.revokeObjectURL(url);
  }

  // Helper for template
  Math = Math;
}
