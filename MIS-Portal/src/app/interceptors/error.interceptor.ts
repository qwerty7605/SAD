import { HttpInterceptorFn, HttpErrorResponse } from '@angular/common/http';
import { inject } from '@angular/core';
import { Router } from '@angular/router';
import { catchError, throwError } from 'rxjs';

/**
 * Error Interceptor - Handles HTTP errors globally
 *
 * This interceptor catches HTTP errors and handles them appropriately:
 * - 401: Unauthorized - redirects to login
 * - 403: Forbidden - shows error message
 * - 500: Server error - shows error message
 */
export const errorInterceptor: HttpInterceptorFn = (req, next) => {
  const router = inject(Router);

  return next(req).pipe(
    catchError((error: HttpErrorResponse) => {
      let errorMessage = 'An error occurred';

      if (error.error instanceof ErrorEvent) {
        // Client-side error
        errorMessage = `Error: ${error.error.message}`;
      } else {
        // Server-side error
        switch (error.status) {
          case 401:
            // Unauthorized - clear token and redirect to login
            localStorage.removeItem('auth_token');
            router.navigate(['/login']);
            errorMessage = 'Session expired. Please login again.';
            break;
          case 403:
            errorMessage = 'You do not have permission to access this resource.';
            break;
          case 404:
            errorMessage = 'Resource not found.';
            break;
          case 500:
            errorMessage = 'Server error. Please try again later.';
            break;
          default:
            errorMessage = error.error?.message || `Error Code: ${error.status}`;
        }
      }

      console.error('HTTP Error:', errorMessage, error);
      return throwError(() => new Error(errorMessage));
    })
  );
};
