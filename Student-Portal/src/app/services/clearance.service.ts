import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { environment } from '../../environments/environment';
import { Clearance } from '../models/clearance.model';
import { Observable } from 'rxjs';
import { ApiService } from './api.service';

@Injectable({
  providedIn: 'root',
})
export class ClearanceService {
  private apiService = inject(ApiService);

  /**
   * Get all clearances for the current student
   */
  getStudentClearances(): Observable<Clearance[]> {
    return this.apiService.get<Clearance[]>('student/clearances');
  }

  /**
   * Get clearance summary for the current student
   */
  getClearanceSummary(): Observable<any> {
    return this.apiService.get('student/clearances/summary');
  }
}
