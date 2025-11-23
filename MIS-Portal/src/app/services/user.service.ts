import { Injectable, inject } from '@angular/core';
import { Observable } from 'rxjs';
import { ApiService } from './api.service';
import { User } from '../models/user.model';

/**
 * User Service - Handles user management operations for MIS admins
 *
 * Example of how to use ApiService for specific functionality
 */
@Injectable({
  providedIn: 'root'
})
export class UserService {
  private apiService = inject(ApiService);

  /**
   * Get all users with optional filters
   */
  getUsers(filters?: any): Observable<User[]> {
    return this.apiService.get<User[]>('users', filters);
  }

  /**
   * Get specific user by ID
   */
  getUser(id: number): Observable<User> {
    return this.apiService.get<User>(`users/${id}`);
  }

  /**
   * Create new user
   */
  createUser(userData: Partial<User>): Observable<User> {
    return this.apiService.post<User>('users', userData);
  }

  /**
   * Update user
   */
  updateUser(id: number, userData: Partial<User>): Observable<User> {
    return this.apiService.put<User>(`users/${id}`, userData);
  }

  /**
   * Delete user
   */
  deleteUser(id: number): Observable<any> {
    return this.apiService.delete(`users/${id}`);
  }

  /**
   * Get user statistics
   */
  getUserStatistics(): Observable<any> {
    return this.apiService.get('users/statistics');
  }

  /**
   * Bulk import users
   */
  bulkImport(file: File): Observable<any> {
    const formData = new FormData();
    formData.append('file', file);
    return this.apiService.upload('users/import', formData);
  }
}
