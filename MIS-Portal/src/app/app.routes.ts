import { Routes } from '@angular/router';
import { authGuard } from './guards/auth.guard';

// Component imports (will be created in subsequent tasks)
import { LoginComponent } from './components/login/login.component';
import { DashboardComponent } from './components/dashboard/dashboard.component';
import { StudentsComponent } from './components/students/students.component';
import { OrgAdminsComponent } from './components/org-admins/org-admins.component';
import { SysAdminsComponent } from './components/sys-admins/sys-admins.component';
import { OrganizationsComponent } from './components/organizations/organizations.component';
import { ClearancesComponent } from './components/clearances/clearances.component';
import { TermsComponent } from './components/terms/terms.component';
import { AuditLogsComponent } from './components/audit-logs/audit-logs.component';
import { SettingsComponent } from './components/settings/settings.component';

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
    path: 'students',
    component: StudentsComponent,
    canActivate: [authGuard]
  },
  {
    path: 'org-admins',
    component: OrgAdminsComponent,
    canActivate: [authGuard]
  },
  {
    path: 'sys-admins',
    component: SysAdminsComponent,
    canActivate: [authGuard]
  },
  {
    path: 'organizations',
    component: OrganizationsComponent,
    canActivate: [authGuard]
  },
  {
    path: 'clearances',
    component: ClearancesComponent,
    canActivate: [authGuard]
  },
  {
    path: 'terms',
    component: TermsComponent,
    canActivate: [authGuard]
  },
  {
    path: 'audit-logs',
    component: AuditLogsComponent,
    canActivate: [authGuard]
  },
  {
    path: 'settings',
    component: SettingsComponent,
    canActivate: [authGuard]
  },

  // Wildcard route - redirects to login
  { path: '**', redirectTo: '/login' }
];
