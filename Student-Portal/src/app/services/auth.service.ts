import { Injectable, inject, signal } from '@angular/core';
import { Router } from '@angular/router';
import { Observable, tap } from 'rxjs';
import { ApiService } from './api.service';

export interface LoginCredentials {
  email: string;
  password: string;
}

export interface RegisterData {
  name: string;
  email: string;
  password: string;
  password_confirmation: string;
  student_id?: string;
}

export interface AuthResponse {
  token: string;
  user: any;
}

/**
 * Authentication Service - Handles user authentication
 *
 * Manages login, logout, registration, and user session
 */
@Injectable({
  providedIn: 'root'
})
export class AuthService {
  private apiService = inject(ApiService);
  private router = inject(Router);

  // Signal for current user state
  currentUser = signal<any>(null);
  isAuthenticated = signal<boolean>(false);

  constructor() {
    // Check if user is already logged in
    this.checkAuth();
  }

  /**
   * Check if user is authenticated
   */
  private checkAuth(): void {
    const token = localStorage.getItem('auth_token');
    const user = localStorage.getItem('user');

    if (token && user) {
      this.currentUser.set(JSON.parse(user));
      this.isAuthenticated.set(true);
    }
  }

  /**
   * Login user
   */
  login(credentials: LoginCredentials): Observable<AuthResponse> {
    return this.apiService.post<AuthResponse>('login', credentials).pipe(
      tap(response => {
        this.setSession(response);
      })
    );
  }

  /**
   * Register new user
   */
  register(data: RegisterData): Observable<AuthResponse> {
    return this.apiService.post<AuthResponse>('register', data).pipe(
      tap(response => {
        this.setSession(response);
      })
    );
  }

  /**
   * Logout user
   */
  logout(): Observable<any> {
    return this.apiService.post('logout', {}).pipe(
      tap(() => {
        this.clearSession();
      })
    );
  }

  /**
   * Set user session
   */
  private setSession(authResponse: AuthResponse): void {
    localStorage.setItem('auth_token', authResponse.token);
    localStorage.setItem('user', JSON.stringify(authResponse.user));
    this.currentUser.set(authResponse.user);
    this.isAuthenticated.set(true);
  }

  /**
   * Clear user session
   */
  private clearSession(): void {
    localStorage.removeItem('auth_token');
    localStorage.removeItem('user');
    this.currentUser.set(null);
    this.isAuthenticated.set(false);
    this.router.navigate(['/login']);
  }

  /**
   * Get current user
   */
  getUser(): any {
    return this.currentUser();
  }

  /**
   * Check if user is logged in
   */
  isLoggedIn(): boolean {
    return this.isAuthenticated();
  }

  /**
   * Get auth token
   */
  getToken(): string | null {
    return localStorage.getItem('auth_token');
  }
}
