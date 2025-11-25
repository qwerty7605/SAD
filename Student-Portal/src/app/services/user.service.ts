import { Injectable, inject } from '@angular/core';
import { Observable } from 'rxjs';
import { ApiService } from './api.service';

/**
 * User Service - Handles student profile operations
 */
@Injectable({
  providedIn: 'root',
})
export class UserService {
  private apiService = inject(ApiService);

  /**
   * Get current student's profile
   */
  getProfile(): Observable<any> {
    return this.apiService.get('student/profile');
  }

  /**
   * Get student clearance summary
   */
  getClearanceSummary(): Observable<any> {
    return this.apiService.get('student/clearances/summary');
  }
}
