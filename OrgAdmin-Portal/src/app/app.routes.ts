import { Routes } from '@angular/router';
import { authGuard } from './guards/auth.guard';

// Component imports
import { LoginComponent } from './components/login/login.component';
import { DashboardComponent } from './components/dashboard/dashboard.component';
import { ClearanceListComponent } from './components/clearance-list/clearance-list.component';

export const routes: Routes = [
  // Default route redirects to login
  { path: '', redirectTo: '/login', pathMatch: 'full' },

  // Public routes
  { path: 'login', component: LoginComponent },

  // Protected routes (require authentication)
  {
    path: 'dashboard',
    component: DashboardComponent,
    canActivate: [authGuard]
  },
  {
    path: 'clearances',
    component: ClearanceListComponent,
    canActivate: [authGuard]
  },

  // Wildcard route - redirects to login
  { path: '**', redirectTo: '/login' }
];
