import { Injectable, inject } from '@angular/core';
import { HttpClient, HttpHeaders, HttpParams } from '@angular/common/http';
import { Observable } from 'rxjs';
import { getApiUrl } from '../../environments/environment';

/**
 * Base API Service - Handles all HTTP communications with Laravel backend
 *
 * Provides generic CRUD methods for interacting with the API
 * All specific services should use this service or extend it
 */
@Injectable({
  providedIn: 'root'
})
export class ApiService {
  private http = inject(HttpClient);
  private apiUrl = getApiUrl() + '/api';

  /**
   * Normalize endpoint path to avoid duplicate slashes.
   */
  private buildUrl(endpoint: string): string {
    const normalized = endpoint.replace(/^\/+/, '');
    return `${this.apiUrl}/${normalized}`;
  }

  /**
   * GET request
   */
  get<T>(endpoint: string, params?: HttpParams | any): Observable<T> {
    // Convert plain object to HttpParams if needed
    let httpParams: HttpParams | undefined;
    if (params) {
      if (params instanceof HttpParams) {
        httpParams = params;
      } else {
        httpParams = new HttpParams();
        Object.keys(params).forEach(key => {
          if (params[key] !== null && params[key] !== undefined) {
            httpParams = httpParams!.set(key, params[key].toString());
          }
        });
      }
    }
    return this.http.get<T>(this.buildUrl(endpoint), { params: httpParams });
  }

  /**
   * POST request
   */
  post<T>(endpoint: string, data: any): Observable<T> {
    return this.http.post<T>(this.buildUrl(endpoint), data);
  }

  /**
   * PUT request
   */
  put<T>(endpoint: string, data: any): Observable<T> {
    return this.http.put<T>(this.buildUrl(endpoint), data);
  }

  /**
   * PATCH request
   */
  patch<T>(endpoint: string, data: any): Observable<T> {
    return this.http.patch<T>(this.buildUrl(endpoint), data);
  }

  /**
   * DELETE request
   */
  delete<T>(endpoint: string): Observable<T> {
    return this.http.delete<T>(this.buildUrl(endpoint));
  }

  /**
   * Upload file with FormData
   */
  upload<T>(endpoint: string, formData: FormData): Observable<T> {
    return this.http.post<T>(this.buildUrl(endpoint), formData);
  }

  /**
   * Download file
   */
  download(endpoint: string): Observable<Blob> {
    return this.http.get(this.buildUrl(endpoint), {
      responseType: 'blob'
    });
  }
}
