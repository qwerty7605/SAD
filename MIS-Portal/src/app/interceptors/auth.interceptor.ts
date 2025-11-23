import { HttpInterceptorFn } from '@angular/common/http';

/**
 * Auth Interceptor - Adds JWT token to outgoing requests
 *
 * This interceptor automatically attaches the authentication token
 * from localStorage to all HTTP requests to the API
 */
export const authInterceptor: HttpInterceptorFn = (req, next) => {
  // Get token from localStorage
  const token = localStorage.getItem('auth_token');

  // If token exists, clone the request and add Authorization header
  if (token) {
    const clonedRequest = req.clone({
      setHeaders: {
        Authorization: `Bearer ${token}`
      }
    });
    return next(clonedRequest);
  }

  // If no token, proceed with original request
  return next(req);
};
