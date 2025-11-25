import { Component, OnInit, signal, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { MISService } from '../../services/mis.service';
import { Organization, OrganizationFormData } from '../../models/organization.model';

@Component({
  selector: 'app-organizations',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './organizations.component.html',
  styleUrl: './organizations.component.scss'
})
export class OrganizationsComponent implements OnInit {
  private misService = inject(MISService);

  // State
  organizations = signal<Organization[]>([]);
  isLoading = signal(false);
  errorMessage = signal('');
  total = signal(0);

  // Filters
  searchQuery = signal('');
  statusFilter = signal<'all' | 'active' | 'inactive'>('all');
  categoryFilter = signal<'all' | 'academic' | 'administrative' | 'finance' | 'student_services'>('all');

  // Pagination
  currentPage = signal(1);
  perPage = signal(15);
  totalPages = signal(0);

  // Modal state
  showModal = signal(false);
  modalMode = signal<'create' | 'edit'>('create');
  selectedOrganization = signal<Organization | null>(null);

  // Form data
  formData = signal<OrganizationFormData>({
    org_name: '',
    org_code: '',
    org_type: 'academic',
    department: '',
    requires_clearance: false,
    is_active: true
  });

  // Delete confirmation
  showDeleteModal = signal(false);
  organizationToDelete = signal<Organization | null>(null);

  ngOnInit() {
    this.loadOrganizations();
  }

  loadOrganizations() {
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

    if (this.categoryFilter() !== 'all') {
      params.org_type = this.categoryFilter();
    }

    this.misService.getOrganizations(params).subscribe({
      next: (response) => {
        this.organizations.set(response.data);
        this.total.set(response.total);
        this.totalPages.set(Math.ceil(response.total / this.perPage()));
        this.isLoading.set(false);
      },
      error: (error) => {
        this.errorMessage.set(error.message || 'Failed to load organizations');
        this.isLoading.set(false);
      }
    });
  }

  onSearch() {
    this.currentPage.set(1);
    this.loadOrganizations();
  }

  onFilterChange() {
    this.currentPage.set(1);
    this.loadOrganizations();
  }

  nextPage() {
    if (this.currentPage() < this.totalPages()) {
      this.currentPage.update(page => page + 1);
      this.loadOrganizations();
    }
  }

  previousPage() {
    if (this.currentPage() > 1) {
      this.currentPage.update(page => page - 1);
      this.loadOrganizations();
    }
  }

  openCreateModal() {
    this.modalMode.set('create');
    this.formData.set({
      org_name: '',
      org_code: '',
      org_type: 'academic',
      department: '',
      requires_clearance: false,
      is_active: true
    });
    this.showModal.set(true);
  }

  openEditModal(organization: Organization) {
    this.modalMode.set('edit');
    this.selectedOrganization.set(organization);
    this.formData.set({
      org_name: organization.org_name,
      org_code: organization.org_code,
      org_type: organization.org_type,
      department: organization.department,
      requires_clearance: organization.requires_clearance,
      is_active: organization.is_active
    });
    this.showModal.set(true);
  }

  closeModal() {
    this.showModal.set(false);
    this.selectedOrganization.set(null);
  }

  onSubmit() {
    const data = this.formData();

    if (this.modalMode() === 'create') {
      this.misService.createOrganization(data).subscribe({
        next: () => {
          this.closeModal();
          this.loadOrganizations();
        },
        error: (error) => {
          this.errorMessage.set(error.message || 'Failed to create organization');
        }
      });
    } else {
      const organizationId = this.selectedOrganization()?.org_id;
      if (organizationId) {
        this.misService.updateOrganization(organizationId, data).subscribe({
          next: () => {
            this.closeModal();
            this.loadOrganizations();
          },
          error: (error) => {
            this.errorMessage.set(error.message || 'Failed to update organization');
          }
        });
      }
    }
  }

  openDeleteModal(organization: Organization) {
    this.organizationToDelete.set(organization);
    this.showDeleteModal.set(true);
  }

  closeDeleteModal() {
    this.showDeleteModal.set(false);
    this.organizationToDelete.set(null);
  }

  confirmDelete() {
    const organization = this.organizationToDelete();
    if (organization) {
      this.misService.deleteOrganization(organization.org_id).subscribe({
        next: () => {
          this.closeDeleteModal();
          this.loadOrganizations();
        },
        error: (error) => {
          this.errorMessage.set(error.message || 'Failed to delete organization');
          this.closeDeleteModal();
        }
      });
    }
  }

  getCategoryLabel(category: string): string {
    const labels: { [key: string]: string } = {
      'academic': 'Academic',
      'administrative': 'Administrative',
      'finance': 'Finance',
      'student_services': 'Student Services'
    };
    return labels[category] || category;
  }

  // Helper for template
  Math = Math;
}
