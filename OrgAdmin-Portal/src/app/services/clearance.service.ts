import { Injectable, inject } from '@angular/core';
import { Observable } from 'rxjs';
import { ApiService } from './api.service';
import { Clearance, ClearanceItem, ClearanceStatus } from '../models/clearance.model';

/**
 * Clearance Service - Handles clearance review operations for org admins
 *
 * Example of how to use ApiService for specific functionality
 */
@Injectable({
  providedIn: 'root'
})
export class ClearanceService {
  private apiService = inject(ApiService);

  /**
   * Get all pending clearances for the organization
   */
  getPendingClearances(): Observable<ClearanceItem[]> {
    return this.apiService.get<ClearanceItem[]>('clearances/pending');
  }

  /**
   * Get specific clearance by ID
   */
  getClearance(id: number): Observable<Clearance> {
    return this.apiService.get<Clearance>(`clearances/${id}`);
  }

  /**
   * Approve clearance
   */
  approveClearance(id: number, remarks?: string): Observable<Clearance> {
    return this.apiService.patch<Clearance>(`clearances/${id}/approve`, {
      remarks
    });
  }

  /**
   * Reject clearance
   */
  rejectClearance(id: number, remarks: string): Observable<Clearance> {
    return this.apiService.patch<Clearance>(`clearances/${id}/reject`, {
      remarks
    });
  }

  /**
   * Bulk approve clearances
   */
  bulkApprove(clearanceIds: number[]): Observable<any> {
    return this.apiService.post('clearances/bulk-approve', {
      clearance_ids: clearanceIds
    });
  }

  /**
   * Get clearance statistics for the organization
   */
  getStatistics(): Observable<any> {
    return this.apiService.get('clearances/statistics');
  }

  /**
   * Generate report
   */
  generateReport(filters?: any): Observable<Blob> {
    return this.apiService.download('clearances/report');
  }
}
