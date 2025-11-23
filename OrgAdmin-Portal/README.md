# Organization Admin Portal

Angular-based frontend for the LNU Online Clearance System - Organization Administrators interface.

This portal is designed for organization administrators (departments, offices, libraries, student organizations, etc.) to manage student clearance requests.

## Purpose

The OrgAdmin Portal allows organization representatives to:
- Review student clearance submissions
- Approve or reject clearance requests with comments
- Manage organization-specific clearance requirements
- Generate reports for their organization
- Bulk process clearance requests
- Track clearance statistics

## Technology Stack

- **Framework**: Angular 20.3.0
- **Language**: TypeScript 5.9.2
- **Styling**: SCSS
- **Backend API**: Laravel (shared with other portals)

## Development Server

To start the development server, run:

```bash
ng serve --port 4201
```

The portal will be available at `http://localhost:4201/`

**Note**: This portal runs on port **4201** to avoid conflicts with:
- Student Portal (port 4200)
- MIS Portal (port 4202)

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

## Related Projects

This portal is part of the LNU Online Clearance System:
- **Student Portal** (port 4200) - Student-facing interface
- **MIS Portal** (port 4202) - Management Information System interface
- **Laravel Backend** (port 8000) - Shared REST API

See the main project README at the root directory for complete system architecture.

## Additional Resources

For more information on using Angular CLI: [Angular CLI Documentation](https://angular.dev/tools/cli)

---

**Part of**: LNU Online Clearance System
**Portal Type**: Organization Admin Interface
**Port**: 4201
