import { Component, OnInit, signal, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { MISService } from '../../services/mis.service';
import { OrgAdmin, OrgAdminFormData } from '../../models/org-admin.model';
import { Organization } from '../../models/organization.model';

@Component({
  selector: 'app-org-admins',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './org-admins.component.html',
  styleUrl: './org-admins.component.scss'
})
export class OrgAdminsComponent implements OnInit {
  private misService = inject(MISService);

  // State
  orgAdmins = signal<OrgAdmin[]>([]);
  organizations = signal<Organization[]>([]);
  isLoading = signal(false);
  errorMessage = signal('');
  total = signal(0);

  // Filters
  searchQuery = signal('');
  statusFilter = signal<'all' | 'active' | 'inactive'>('all');
  organizationFilter = signal<number | ''>('');

  // Pagination
  currentPage = signal(1);
  perPage = signal(15);
  totalPages = signal(0);

  // Modal state
  showModal = signal(false);
  modalMode = signal<'create' | 'edit'>('create');
  selectedOrgAdmin = signal<OrgAdmin | null>(null);

  // Form data
  formData = signal<OrgAdminFormData>({
    org_id: 0,
    full_name: '',
    position: '',
    is_active: true
  });

  // Delete confirmation
  showDeleteModal = signal(false);
  orgAdminToDelete = signal<OrgAdmin | null>(null);

  ngOnInit() {
    this.loadOrganizations();
    this.loadOrgAdmins();
  }

  loadOrganizations() {
    // Load all organizations for the dropdown
    this.misService.getOrganizations({ per_page: 1000, is_active: 1 }).subscribe({
      next: (response) => {
        this.organizations.set(response.data);
      },
      error: (error) => {
        console.error('Failed to load organizations:', error);
      }
    });
  }

  loadOrgAdmins() {
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

    if (this.organizationFilter()) {
      params.org_id = this.organizationFilter();
    }

    this.misService.getOrgAdmins(params).subscribe({
      next: (response) => {
        this.orgAdmins.set(response.data);
        this.total.set(response.total);
        this.totalPages.set(Math.ceil(response.total / this.perPage()));
        this.isLoading.set(false);
      },
      error: (error) => {
        this.errorMessage.set(error.message || 'Failed to load organization admins');
        this.isLoading.set(false);
      }
    });
  }

  onSearch() {
    this.currentPage.set(1);
    this.loadOrgAdmins();
  }

  onFilterChange() {
    this.currentPage.set(1);
    this.loadOrgAdmins();
  }

  nextPage() {
    if (this.currentPage() < this.totalPages()) {
      this.currentPage.update(page => page + 1);
      this.loadOrgAdmins();
    }
  }

  previousPage() {
    if (this.currentPage() > 1) {
      this.currentPage.update(page => page - 1);
      this.loadOrgAdmins();
    }
  }

  openCreateModal() {
    this.modalMode.set('create');
    this.formData.set({
      org_id: 0,
      full_name: '',
      position: '',
      is_active: true
    });
    this.showModal.set(true);
  }

  openEditModal(orgAdmin: OrgAdmin) {
    this.modalMode.set('edit');
    this.selectedOrgAdmin.set(orgAdmin);
    this.formData.set({
      org_id: orgAdmin.org_id,
      full_name: orgAdmin.full_name,
      position: orgAdmin.position,
      is_active: orgAdmin.is_active
    });
    this.showModal.set(true);
  }

  closeModal() {
    this.showModal.set(false);
    this.selectedOrgAdmin.set(null);
  }

  onSubmit() {
    const data = this.formData();

    if (this.modalMode() === 'create') {
      this.misService.createOrgAdmin(data).subscribe({
        next: () => {
          this.closeModal();
          this.loadOrgAdmins();
        },
        error: (error) => {
          this.errorMessage.set(error.message || 'Failed to create organization admin');
        }
      });
    } else {
      const adminId = this.selectedOrgAdmin()?.admin_id;
      if (adminId) {
        this.misService.updateOrgAdmin(adminId, data).subscribe({
          next: () => {
            this.closeModal();
            this.loadOrgAdmins();
          },
          error: (error) => {
            this.errorMessage.set(error.message || 'Failed to update organization admin');
          }
        });
      }
    }
  }

  openDeleteModal(orgAdmin: OrgAdmin) {
    this.orgAdminToDelete.set(orgAdmin);
    this.showDeleteModal.set(true);
  }

  closeDeleteModal() {
    this.showDeleteModal.set(false);
    this.orgAdminToDelete.set(null);
  }

  confirmDelete() {
    const orgAdmin = this.orgAdminToDelete();
    if (orgAdmin) {
      this.misService.deleteOrgAdmin(orgAdmin.admin_id).subscribe({
        next: () => {
          this.closeDeleteModal();
          this.loadOrgAdmins();
        },
        error: (error) => {
          this.errorMessage.set(error.message || 'Failed to delete organization admin');
          this.closeDeleteModal();
        }
      });
    }
  }


  // Helper for template
  Math = Math;
}
