# HTTP Client Setup Guide

Complete HTTP setup for all three Angular portals in the LNU Online Clearance System.

## What Was Configured

All three portals (Student, OrgAdmin, MIS) now have:

1. **HttpClient** configured with interceptors
2. **Authentication & Error interceptors**
3. **Base API Service** for HTTP operations
4. **Auth Service** for user authentication
5. **Route Guards** for protected routes
6. **TypeScript Models** for data structures
7. **Example services** showing how to use the API

## Project Structure

```
src/app/
├── guards/
│   └── auth.guard.ts              # Route protection
├── interceptors/
│   ├── auth.interceptor.ts        # Adds JWT token to requests
│   └── error.interceptor.ts       # Handles HTTP errors
├── models/
│   ├── user.model.ts              # User interfaces
│   ├── clearance.model.ts         # Clearance interfaces
│   └── organization.model.ts      # Organization interfaces
└── services/
    ├── api.service.ts             # Base HTTP service
    ├── auth.service.ts            # Authentication service
    └── clearance.service.ts       # Example domain service
```

## How It Works

### 1. API Service (Base HTTP Operations)

The `ApiService` provides generic HTTP methods:

```typescript
import { ApiService } from './services/api.service';

constructor(private apiService = inject(ApiService)) {}

// GET request
this.apiService.get<User[]>('users');

// POST request
this.apiService.post<User>('users', userData);

// PUT request
this.apiService.put<User>('users/1', userData);

// PATCH request
this.apiService.patch<User>('users/1', { name: 'New Name' });

// DELETE request
this.apiService.delete('users/1');

// Upload file
this.apiService.upload('files', formData);

// Download file
this.apiService.download('reports/monthly');
```

### 2. Authentication Service

Handles login, logout, and session management:

```typescript
import { AuthService } from './services/auth.service';

constructor(private authService = inject(AuthService)) {}

// Login
this.authService.login({ email, password }).subscribe({
  next: (response) => {
    console.log('Login successful', response);
    // Token is automatically saved
  },
  error: (error) => console.error('Login failed', error)
});

// Logout
this.authService.logout().subscribe({
  next: () => console.log('Logged out')
});

// Check if logged in
if (this.authService.isLoggedIn()) {
  // User is authenticated
}

// Get current user
const user = this.authService.getUser();
```

### 3. Auth Interceptor

Automatically adds JWT token to **all** API requests:

```
Authorization: Bearer <token>
```

The token is stored in `localStorage` as `auth_token`.

### 4. Error Interceptor

Handles HTTP errors globally:

- **401 Unauthorized**: Clears token, redirects to login
- **403 Forbidden**: Shows permission error
- **404 Not Found**: Shows not found error
- **500 Server Error**: Shows server error

### 5. Route Guard

Protect routes that require authentication:

```typescript
// app.routes.ts
import { authGuard } from './guards/auth.guard';

export const routes: Routes = [
  { path: 'login', component: LoginComponent },
  {
    path: 'dashboard',
    component: DashboardComponent,
    canActivate: [authGuard]  // Protected route
  }
];
```

## Creating New Services

Example: Create a new service for a specific domain

```typescript
import { Injectable, inject } from '@angular/core';
import { Observable } from 'rxjs';
import { ApiService } from './api.service';

@Injectable({
  providedIn: 'root'
})
export class MyService {
  private apiService = inject(ApiService);

  getItems(): Observable<any[]> {
    return this.apiService.get<any[]>('items');
  }

  createItem(data: any): Observable<any> {
    return this.apiService.post<any>('items', data);
  }
}
```

## Example: Login Component

```typescript
import { Component, inject } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { Router } from '@angular/router';
import { AuthService } from './services/auth.service';

@Component({
  selector: 'app-login',
  standalone: true,
  imports: [FormsModule],
  template: `
    <form (ngSubmit)="onSubmit()">
      <input [(ngModel)]="email" name="email" type="email" required>
      <input [(ngModel)]="password" name="password" type="password" required>
      <button type="submit">Login</button>
    </form>
  `
})
export class LoginComponent {
  private authService = inject(AuthService);
  private router = inject(Router);

  email = '';
  password = '';

  onSubmit() {
    this.authService.login({ email: this.email, password: this.password })
      .subscribe({
        next: () => {
          this.router.navigate(['/dashboard']);
        },
        error: (error) => {
          alert('Login failed: ' + error.message);
        }
      });
  }
}
```

## Example: Using Clearance Service

```typescript
import { Component, inject, OnInit } from '@angular/core';
import { ClearanceService } from './services/clearance.service';
import { ClearanceItem } from './models/clearance.model';

@Component({
  selector: 'app-clearances',
  standalone: true,
  template: `
    <div *ngFor="let clearance of clearances">
      {{ clearance.organization_name }} - {{ clearance.status }}
    </div>
  `
})
export class ClearancesComponent implements OnInit {
  private clearanceService = inject(ClearanceService);
  clearances: ClearanceItem[] = [];

  ngOnInit() {
    this.clearanceService.getMyClearances().subscribe({
      next: (data) => {
        this.clearances = data;
      },
      error: (error) => {
        console.error('Error fetching clearances:', error);
      }
    });
  }
}
```

## TypeScript Models

Models are defined for type safety:

```typescript
// Using models in your code
import { User, UserRole } from './models/user.model';
import { Clearance, ClearanceStatus } from './models/clearance.model';

const user: User = {
  id: 1,
  name: 'John Doe',
  email: 'john@example.com',
  role: UserRole.STUDENT,
  created_at: '2025-01-01',
  updated_at: '2025-01-01'
};
```

## Environment Configuration

API URLs are configured in environment files:

**Development**: `src/environments/environment.ts`
```typescript
export const environment = {
  production: false,
  apiUrl: 'http://localhost:8000'
};
```

**Production**: `src/environments/environment.prod.ts`
```typescript
export const environment = {
  production: true,
  apiUrl: 'https://api.lnu-clearance.edu.ph'
};
```

## Laravel Backend Requirements

Your Laravel backend should provide these endpoints:

### Authentication
```
POST   /api/login           - Login user
POST   /api/logout          - Logout user
POST   /api/register        - Register new user
```

### Example Response Format
```json
{
  "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "role": "student"
  }
}
```

## CORS Configuration in Laravel

Add to `config/cors.php`:

```php
'paths' => ['api/*'],
'allowed_origins' => [
    'http://localhost:4200',  // Student Portal
    'http://localhost:4201',  // OrgAdmin Portal
    'http://localhost:4202',  // MIS Portal
],
'allowed_methods' => ['*'],
'allowed_headers' => ['*'],
'exposed_headers' => [],
'max_age' => 0,
'supports_credentials' => true,
```

## Testing the Setup

### 1. Start Laravel Backend
```bash
cd Laravel
php artisan serve
```

### 2. Start Angular Portal
```bash
cd Student-Portal
ng serve --port 4200
```

### 3. Test API Call
Open browser console and try:
```javascript
fetch('http://localhost:8000/api/test')
  .then(r => r.json())
  .then(console.log)
```

## Common Patterns

### Handle Loading State
```typescript
isLoading = false;

getData() {
  this.isLoading = true;
  this.apiService.get('data').subscribe({
    next: (data) => {
      this.isLoading = false;
      // Handle data
    },
    error: (error) => {
      this.isLoading = false;
      // Handle error
    }
  });
}
```

### Handle Pagination
```typescript
import { HttpParams } from '@angular/common/http';

getUsers(page: number = 1, perPage: number = 10) {
  const params = new HttpParams()
    .set('page', page.toString())
    .set('per_page', perPage.toString());

  return this.apiService.get<User[]>('users', params);
}
```

### Upload File
```typescript
uploadFile(file: File) {
  const formData = new FormData();
  formData.append('file', file);
  formData.append('description', 'My file');

  return this.apiService.upload('files', formData);
}
```

## Next Steps

1. **Build Login/Register components** for each portal
2. **Create dashboard components** using the services
3. **Add more specific services** (OrganizationService, etc.)
4. **Implement error notifications** (toast/snackbar)
5. **Add loading indicators** throughout the app
6. **Create Laravel API endpoints** matching the service calls

## Verification

All three portals have been tested and compile successfully:

- ✅ Student-Portal: Compiles successfully
- ✅ OrgAdmin-Portal: Compiles successfully
- ✅ MIS-Portal: Compiles successfully

---

**Ready to build features!** The HTTP foundation is complete and working.
