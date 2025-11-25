import { Component, OnInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { UserService } from '../../services/user.service';

@Component({
  selector: 'app-dashboard',
  standalone: true,
  imports: [CommonModule, RouterModule],
  providers: [UserService],
  templateUrl: './dashboard.html',
  styleUrls: ['./dashboard.scss'],
})
export class DashboardComponent implements OnInit {
  user: any = {};
  menuOpen: boolean = false;

  constructor(private userService: UserService) {}

  ngOnInit() {
    // Set default values
    this.user = {
      name: '',
      studentNumber: '',
      contact: '',
      email: '',
      program: '',
      yearLevel: '',
      enrollmentStatus: 'Pending',
      clearanceStatus: 'Pending',
      semester: 'First Semester, AY 2024-2025',
      greeting: 'Good morning!',
      studentType: 'Regular',
      schedule: 'To be announced'
    };

    // Get student profile
    this.userService.getProfile().subscribe({
      next: (profile) => {
        this.user = {
          ...this.user,
          ...profile
        };
      },
      error: (err) => {
        console.error('Error fetching profile:', err);
      }
    });

    // Get clearance summary
    this.userService.getClearanceSummary().subscribe({
      next: (summary) => {
        this.user.clearanceStatus = summary.clearance_status || 'Pending';
      },
      error: (err) => {
        console.error('Error fetching clearance summary:', err);
      }
    });
  }
}
