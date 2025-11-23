import { Injectable, inject } from '@angular/core';
import { Observable } from 'rxjs';
import { ApiService } from './api.service';
import { Clearance, ClearanceItem, ClearanceStatus } from '../models/clearance.model';

/**
 * Clearance Service - Handles clearance-related operations
 *
 * Example of how to use ApiService for specific functionality
 */
@Injectable({
  providedIn: 'root'
})
export class ClearanceService {
  private apiService = inject(ApiService);

  /**
   * Get all clearances for current student
   */
  getMyClearances(): Observable<ClearanceItem[]> {
    return this.apiService.get<ClearanceItem[]>('clearances');
  }

  /**
   * Get specific clearance by ID
   */
  getClearance(id: number): Observable<Clearance> {
    return this.apiService.get<Clearance>(`clearances/${id}`);
  }

  /**
   * Submit clearance to an organization
   */
  submitClearance(organizationId: number, data?: any): Observable<Clearance> {
    return this.apiService.post<Clearance>('clearances', {
      organization_id: organizationId,
      ...data
    });
  }

  /**
   * Upload document for clearance
   */
  uploadDocument(clearanceId: number, file: File): Observable<any> {
    const formData = new FormData();
    formData.append('file', file);
    formData.append('clearance_id', clearanceId.toString());

    return this.apiService.upload(`clearances/${clearanceId}/documents`, formData);
  }

  /**
   * Get clearance summary/statistics
   */
  getClearanceSummary(): Observable<any> {
    return this.apiService.get('clearances/summary');
  }

  /**
   * Download clearance certificate
   */
  downloadCertificate(): Observable<Blob> {
    return this.apiService.download('clearances/certificate');
  }
}
