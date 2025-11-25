import { Component, input } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';

@Component({
  selector: 'app-sidebar',
  standalone: true,
  imports: [CommonModule, RouterModule],
  templateUrl: './sidebar.component.html',
  styleUrl: './sidebar.component.scss'
})
export class SidebarComponent {
  isOpen = input<boolean>(true);

  menuItems = [
    {
      label: 'Dashboard',
      icon: '📊',
      route: '/dashboard'
    },
    {
      label: 'User Management',
      icon: '👥',
      children: [
        { label: 'Students', route: '/students' },
        { label: 'Org Admins', route: '/org-admins' },
        { label: 'System Admins', route: '/sys-admins' }
      ]
    },
    {
      label: 'Organizations',
      icon: '🏢',
      route: '/organizations'
    },
    {
      label: 'Clearances',
      icon: '✅',
      route: '/clearances'
    },
    {
      label: 'Academic Terms',
      icon: '📅',
      route: '/terms'
    },
    {
      label: 'Audit Logs',
      icon: '📝',
      route: '/audit-logs'
    },
    {
      label: 'Settings',
      icon: '⚙️',
      route: '/settings'
    }
  ];

  expandedItems: Set<string> = new Set();

  toggleExpand(label: string) {
    if (this.expandedItems.has(label)) {
      this.expandedItems.delete(label);
    } else {
      this.expandedItems.add(label);
    }
  }

  isExpanded(label: string): boolean {
    return this.expandedItems.has(label);
  }
}
