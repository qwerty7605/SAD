import { Component, OnInit, signal, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { MISService } from '../../services/mis.service';
import { SysAdmin, SysAdminFormData } from '../../models/sys-admin.model';

@Component({
  selector: 'app-sys-admins',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './sys-admins.component.html',
  styleUrl: './sys-admins.component.scss'
})
export class SysAdminsComponent implements OnInit {
  private misService = inject(MISService);

  // State
  sysAdmins = signal<SysAdmin[]>([]);
  isLoading = signal(false);
  errorMessage = signal('');
  total = signal(0);

  // Filters
  searchQuery = signal('');
  statusFilter = signal<'all' | 'active' | 'inactive'>('all');
  adminLevelFilter = signal<'all' | 'super_admin' | 'mis_staff'>('all');

  // Pagination
  currentPage = signal(1);
  perPage = signal(15);
  totalPages = signal(0);

  // Modal state
  showModal = signal(false);
  modalMode = signal<'create' | 'edit'>('create');
  selectedSysAdmin = signal<SysAdmin | null>(null);

  // Form data
  formData = signal<SysAdminFormData>({
    full_name: '',
    admin_level: 'mis_staff',
    department: ''
  });

  // Delete confirmation
  showDeleteModal = signal(false);
  sysAdminToDelete = signal<SysAdmin | null>(null);

  ngOnInit() {
    this.loadSysAdmins();
  }

  loadSysAdmins() {
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
      params.is_active = this.statusFilter() === 'active' ? 1 : 0;
    }

    if (this.adminLevelFilter() !== 'all') {
      params.admin_level = this.adminLevelFilter();
    }

    this.misService.getSysAdmins(params).subscribe({
      next: (response) => {
        this.sysAdmins.set(response.data);
        this.total.set(response.total);
        this.totalPages.set(Math.ceil(response.total / this.perPage()));
        this.isLoading.set(false);
      },
      error: (error) => {
        this.errorMessage.set(error.message || 'Failed to load system admins');
        this.isLoading.set(false);
      }
    });
  }

  onSearch() {
    this.currentPage.set(1);
    this.loadSysAdmins();
  }

  onFilterChange() {
    this.currentPage.set(1);
    this.loadSysAdmins();
  }

  nextPage() {
    if (this.currentPage() < this.totalPages()) {
      this.currentPage.update(page => page + 1);
      this.loadSysAdmins();
    }
  }

  previousPage() {
    if (this.currentPage() > 1) {
      this.currentPage.update(page => page - 1);
      this.loadSysAdmins();
    }
  }

  openCreateModal() {
    this.modalMode.set('create');
    this.formData.set({
      full_name: '',
      admin_level: 'mis_staff',
      department: ''
    });
    this.showModal.set(true);
  }

  openEditModal(sysAdmin: SysAdmin) {
    this.modalMode.set('edit');
    this.selectedSysAdmin.set(sysAdmin);
    this.formData.set({
      full_name: sysAdmin.full_name,
      admin_level: sysAdmin.admin_level,
      department: sysAdmin.department
    });
    this.showModal.set(true);
  }

  closeModal() {
    this.showModal.set(false);
    this.selectedSysAdmin.set(null);
  }

  onSubmit() {
    const data = this.formData();

    if (this.modalMode() === 'create') {
      this.misService.createSysAdmin(data).subscribe({
        next: () => {
          this.closeModal();
          this.loadSysAdmins();
        },
        error: (error) => {
          this.errorMessage.set(error.message || 'Failed to create system admin');
        }
      });
    } else {
      const adminId = this.selectedSysAdmin()?.sys_admin_id;
      if (adminId) {
        this.misService.updateSysAdmin(adminId, data).subscribe({
          next: () => {
            this.closeModal();
            this.loadSysAdmins();
          },
          error: (error) => {
            this.errorMessage.set(error.message || 'Failed to update system admin');
          }
        });
      }
    }
  }

  openDeleteModal(sysAdmin: SysAdmin) {
    this.sysAdminToDelete.set(sysAdmin);
    this.showDeleteModal.set(true);
  }

  closeDeleteModal() {
    this.showDeleteModal.set(false);
    this.sysAdminToDelete.set(null);
  }

  confirmDelete() {
    const sysAdmin = this.sysAdminToDelete();
    if (sysAdmin) {
      this.misService.deleteSysAdmin(sysAdmin.sys_admin_id).subscribe({
        next: () => {
          this.closeDeleteModal();
          this.loadSysAdmins();
        },
        error: (error) => {
          this.errorMessage.set(error.message || 'Failed to delete system admin');
          this.closeDeleteModal();
        }
      });
    }
  }

  getAdminLevelLabel(level: string): string {
    const labels: { [key: string]: string } = {
      'super_admin': 'Super Admin',
      'mis_staff': 'MIS Staff'
    };
    return labels[level] || level;
  }

  // Helper for template
  Math = Math;
}
