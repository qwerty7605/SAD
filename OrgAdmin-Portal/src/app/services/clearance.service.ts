import { Injectable, inject } from '@angular/core';
import { Observable } from 'rxjs';
import { HttpParams } from '@angular/common/http';
import { ApiService } from './api.service';
import { ClearanceItem, StudentClearance, ClearanceStatistics } from '../models/clearance.model';

/**
 * Clearance Service - Handles clearance review operations for org admins
 * 
 * Based on ERD structure:
 * - CLEARANCE_ITEMS: Individual organization clearance status
 * - STUDENT_CLEARANCES: Main clearance record per student per term
 */
@Injectable({
  providedIn: 'root'
})
export class ClearanceService {
  private apiService = inject(ApiService);

  /**
   * Get all clearance items for the organization's office
   * Shows all students needing clearance for current term
   */
  getPendingClearances(): Observable<ClearanceItem[]> {
    return this.apiService.get<ClearanceItem[]>('org-admin/clearance-items');
  }

  /**
   * Get clearance items filtered by status
   */
  getClearanceItemsByStatus(status: 'all' | 'pending' | 'approved' | 'needs_compliance'): Observable<ClearanceItem[]> {
    let params = new HttpParams();
    if (status !== 'all') {
      params = params.set('status', status);
    }
    return this.apiService.get<ClearanceItem[]>('org-admin/clearance-items', params);
  }

  /**
   * Get specific clearance item by ID
   */
  getClearanceItem(itemId: number): Observable<ClearanceItem> {
    return this.apiService.get<ClearanceItem>(`org-admin/clearance-items/${itemId}`);
  }

  /**
   * Approve a clearance item
   * Changes status from 'pending' to 'approved'
   */
  approveClearance(itemId: number): Observable<ClearanceItem> {
    return this.apiService.patch<ClearanceItem>(`org-admin/clearance-items/${itemId}/approve`, {});
  }

  /**
   * Mark clearance item as needing compliance
   * Changes status to 'needs_compliance'
   */
  rejectClearance(itemId: number): Observable<ClearanceItem> {
    return this.apiService.patch<ClearanceItem>(`org-admin/clearance-items/${itemId}/needs-compliance`, {});
  }

  /**
   * Bulk approve multiple clearance items
   */
  bulkApprove(itemIds: number[]): Observable<any> {
    return this.apiService.post('org-admin/clearance-items/bulk-approve', {
      item_ids: itemIds
    });
  }

  /**
   * Get clearance statistics for the organization
   */
  getStatistics(): Observable<ClearanceStatistics> {
    return this.apiService.get<ClearanceStatistics>('org-admin/statistics');
  }

  /**
   * Search clearances by student number or name
   */
  searchClearances(query: string): Observable<ClearanceItem[]> {
    const params = new HttpParams().set('search', query);
    return this.apiService.get<ClearanceItem[]>('org-admin/clearance-items', params);
  }
}
