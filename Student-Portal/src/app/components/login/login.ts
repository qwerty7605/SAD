import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { Router } from '@angular/router';
import { AuthService } from '../../services/auth.service';

@Component({
  selector: 'app-login',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './login.html',
  styleUrls: ['./login.scss'],
})
export class LoginComponent {
  tbStudentNumber: string = '';
  tbStudentPassword: string = '';
  errorMessage: string = '';
  loading: boolean = false;

  constructor(private authService: AuthService, private router: Router) {}

  login() {
    this.loading = true;
    this.errorMessage = '';

    this.authService
      .login({
        studentNumber: this.tbStudentNumber,
        password: this.tbStudentPassword,
      })
      .subscribe({
        next: (response: any) => {
          this.loading = false;

          // Save token & user info to localStorage
          localStorage.setItem('auth_token', response.token);
          localStorage.setItem('user', JSON.stringify(response.user));

          // Navigate to dashboard
          this.router.navigate(['/dashboard']);
        },
        error: (err) => {
          this.loading = false;
          this.errorMessage = err?.error?.message || 'Login failed. Please try again.';
        },
      });
  }

  togglePassword(inputId: string) {
    const input = document.getElementById(inputId) as HTMLInputElement;
    input.type = input.type === 'password' ? 'text' : 'password';
  }
}
