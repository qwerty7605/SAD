# MIS Portal (Management Information System)

Angular-based frontend for the LNU Online Clearance System - Administrative interface.

This portal is designed for university administrators and MIS staff to oversee and manage the entire clearance system.

## Purpose

The MIS Portal provides administrators with tools to:
- Monitor system-wide clearance progress and statistics
- Manage users (students, organization admins, MIS users)
- Configure clearance requirements and workflows
- Set up academic periods and clearance cycles
- Generate comprehensive analytics and reports
- Manage system settings and configurations
- Oversee all organizations and their requirements
- Handle escalations and special cases

## Technology Stack

- **Framework**: Angular 20.3.0
- **Language**: TypeScript 5.9.2
- **Styling**: SCSS
- **Backend API**: Laravel (shared with other portals)

## Development Server

To start the development server, run:

```bash
ng serve --port 4202
```

The portal will be available at `http://localhost:4202/`

**Note**: This portal runs on port **4202** to avoid conflicts with:
- Student Portal (port 4200)
- OrgAdmin Portal (port 4201)

## Prerequisites

Before running this portal, ensure:
1. **Laravel backend** is running at `http://localhost:8000`
2. **Node.js** (v18+) is installed
3. **Angular CLI** (v20.3.10) is installed globally: `npm install -g @angular/cli`

## Installation

```bash
npm install
```

## API Configuration

The portal connects to the Laravel backend API. Configuration is in:
- Development: `src/environments/environment.ts`
- Production: `src/environments/environment.prod.ts`

Default development API URL: `http://localhost:8000`

## Building for Production

```bash
ng build --configuration production
```

Build artifacts will be stored in the `dist/` directory.

## Code Scaffolding

Generate a new component:
```bash
ng generate component component-name
```

For a complete list of available schematics:
```bash
ng generate --help
```

## Testing

### Unit Tests
```bash
ng test
```

### End-to-End Tests
```bash
ng e2e
```

## Project Structure

```
src/
├── app/                    # Application components and modules
├── assets/                 # Static assets (images, icons, etc.)
├── environments/           # Environment configurations
└── styles.scss            # Global styles
```

## Key Features to Implement

- Dashboard with system-wide analytics
- User management (CRUD operations)
- Organization management
- Clearance requirement configuration
- Academic period/semester setup
- Reporting and analytics tools
- System audit logs
- Bulk operations and data import/export

## Related Projects

This portal is part of the LNU Online Clearance System:
- **Student Portal** (port 4200) - Student-facing interface
- **OrgAdmin Portal** (port 4201) - Organization administrator interface
- **Laravel Backend** (port 8000) - Shared REST API

See the main project README at the root directory for complete system architecture.

## Additional Resources

For more information on using Angular CLI: [Angular CLI Documentation](https://angular.dev/tools/cli)

---

**Part of**: LNU Online Clearance System
**Portal Type**: MIS Administrative Interface
**Port**: 4202
