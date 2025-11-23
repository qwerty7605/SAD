# LNU Online Clearance System

A comprehensive online clearance management system for Leytre Normal University, built with a microservices frontend architecture and a centralized Laravel backend.

## Project Architecture

This system implements a **multi-portal architecture** with three separate frontend applications connecting to a single Laravel backend API. This approach ensures:

- **Separation of Concerns**: Each portal is isolated with its own codebase and deployment
- **Load Distribution**: Traffic is distributed across multiple frontend servers
- **Independent Scaling**: Each portal can be scaled independently based on usage patterns
- **Role-Based Access**: Clear separation between student, organization admin, and MIS functionalities
- **Maintainability**: Changes to one portal don't affect others

### System Overview

```
┌─────────────────┐     ┌─────────────────┐     ┌─────────────────┐
│ Student Portal  │     │ OrgAdmin Portal │     │  MIS Portal     │
│   (Angular)     │     │   (Angular)     │     │   (Angular)     │
│  Port: 4200     │     │  Port: 4201     │     │  Port: 4202     │
└────────┬────────┘     └────────┬────────┘     └────────┬────────┘
         │                       │                       │
         └───────────────────────┴───────────────────────┘
                                 │
                                 ▼
                    ┌────────────────────────┐
                    │   Laravel Backend API  │
                    │      Port: 8000        │
                    │  (Shared REST API)     │
                    └────────────────────────┘
                                 │
                                 ▼
                        ┌────────────────┐
                        │    Database    │
                        │     (MySQL)    │
                        └────────────────┘
```

## Portal Descriptions

### 1. Student Portal
**Technology**: Angular
**Port**: 4200
**Purpose**: Student-facing interface for clearance management

**Features**:
- View clearance requirements and status
- Submit clearance documents
- Track clearance progress
- Receive notifications about clearance updates
- Print clearance certificate

### 2. Organization Admin Portal
**Technology**: Angular
**Port**: 4201
**Purpose**: Interface for organization administrators (departments, offices, libraries, etc.)

**Features**:
- Review student clearance submissions
- Approve/reject clearance requests
- Manage organization-specific requirements
- Generate reports for their organization
- Bulk clearance processing

### 3. MIS Portal
**Technology**: Angular
**Port**: 4202
**Purpose**: Management Information System interface for university administrators

**Features**:
- System-wide clearance monitoring
- User management (students, org admins, MIS users)
- Configure clearance requirements and workflows
- Generate comprehensive analytics and reports
- Manage academic periods and clearance cycles
- System configuration and settings

### 4. Laravel Backend
**Technology**: Laravel (PHP)
**Port**: 8000
**Purpose**: Centralized API and business logic

**Responsibilities**:
- RESTful API endpoints for all portals
- Authentication and authorization (JWT/Sanctum)
- Business logic and validation
- Database operations
- File storage management
- Email notifications
- Background job processing

## Project Structure

```
Web Dev/
├── Student-Portal/          # Angular frontend for students
│   ├── src/
│   ├── public/
│   ├── angular.json
│   └── package.json
│
├── OrgAdmin-Portal/         # Frontend for organization admins
│   ├── src/
│   └── public/
│
├── MIS-Portal/              # Frontend for MIS administrators
│   ├── src/
│   └── public/
│
├── Laravel/                 # Backend API (Laravel)
│   ├── app/
│   ├── database/
│   ├── routes/
│   └── composer.json
│
└── LNU_Clearance_System_FINAL_ERD.md  # Database schema documentation
```

## Getting Started

### Prerequisites

- **Node.js** (v18+) for frontend portals
- **PHP** (v8.1+) for Laravel backend
- **Composer** for PHP dependencies
- **MySQL** (v8.0+) for database
- **Angular CLI** for Student Portal

### Installation

#### 1. Backend Setup (Laravel)

```bash
cd Laravel
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan serve
```

The API will be available at `http://localhost:8000`

#### 2. Student Portal Setup (Angular)

```bash
cd Student-Portal
npm install
ng serve
```

The portal will be available at `http://localhost:4200`

#### 3. Organization Admin Portal Setup

```bash
cd OrgAdmin-Portal
npm install
ng serve --port 4201
```

The portal will be available at `http://localhost:4201`

#### 4. MIS Portal Setup

```bash
cd MIS-Portal
npm install
ng serve --port 4202
```

The portal will be available at `http://localhost:4202`

## API Integration

All frontend portals communicate with the Laravel backend through RESTful API endpoints:

- **Base URL**: `http://localhost:8000/api`
- **Authentication**: JWT/Laravel Sanctum tokens
- **Data Format**: JSON

### Common API Endpoints

```
Authentication:
POST   /api/login
POST   /api/logout
POST   /api/register

Clearance:
GET    /api/clearances
POST   /api/clearances
GET    /api/clearances/{id}
PUT    /api/clearances/{id}
DELETE /api/clearances/{id}

Students:
GET    /api/students
GET    /api/students/{id}

Organizations:
GET    /api/organizations
POST   /api/organizations
```

See the full API documentation in `Laravel/docs/API.md`

## Environment Configuration

### Student Portal
Create `src/environments/environment.ts`:
```typescript
export const environment = {
  production: false,
  apiUrl: 'http://localhost:8000/api'
};
```

### OrgAdmin Portal
Create `src/environments/environment.ts`:
```typescript
export const environment = {
  production: false,
  apiUrl: 'http://localhost:8000/api'
};
```

### MIS Portal
Create `src/environments/environment.ts`:
```typescript
export const environment = {
  production: false,
  apiUrl: 'http://localhost:8000/api'
};
```

### Laravel Backend
Configure `.env` file:
```env
APP_URL=http://localhost:8000
FRONTEND_STUDENT_URL=http://localhost:4200
FRONTEND_ORGADMIN_URL=http://localhost:3001
FRONTEND_MIS_URL=http://localhost:3002

CORS_ALLOWED_ORIGINS=http://localhost:4200,http://localhost:3001,http://localhost:3002
```

## Development Workflow

1. **Start Backend First**: Always start the Laravel backend before the frontends
2. **Run Migrations**: After database changes, run migrations before testing
3. **API-First Development**: Design API endpoints before implementing frontend features
4. **Shared Models**: Keep data models synchronized across all portals
5. **CORS Configuration**: Ensure CORS is properly configured in Laravel for all frontend URLs

## Deployment

### Production Deployment Strategy

1. **Backend**: Deploy Laravel to a web server (e.g., DigitalOcean, AWS EC2)
2. **Frontend Portals**: Build and deploy each portal separately
   - Student Portal: `ng build --configuration production`
   - OrgAdmin Portal: `npm run build`
   - MIS Portal: `npm run build`
3. **Database**: Set up MySQL on a separate server or managed service
4. **Domain Structure**:
   - API: `api.lnu-clearance.edu.ph`
   - Student: `student.lnu-clearance.edu.ph`
   - OrgAdmin: `orgadmin.lnu-clearance.edu.ph`
   - MIS: `mis.lnu-clearance.edu.ph`

## Load Distribution Benefits

### Why Separate Frontends?

1. **User Segmentation**: Different user types have different peak usage times
2. **Resource Optimization**: Scale only the portals that need it
3. **Failure Isolation**: If one portal goes down, others remain operational
4. **Independent Deployment**: Deploy updates to one portal without affecting others
5. **Security**: Isolate sensitive MIS functions from public-facing student portal

## Database Schema

See `LNU_Clearance_System_FINAL_ERD.md` for the complete database entity-relationship diagram and schema documentation.

## Contributing

### Branch Strategy
- `main` - Production-ready code
- `develop` - Integration branch
- `feature/*` - New features
- `bugfix/*` - Bug fixes

### Code Standards
- Follow framework conventions (Angular style guide, Laravel best practices)
- Write meaningful commit messages
- Add comments for complex logic
- Keep components/controllers focused and single-purpose

## Testing

### Backend Testing
```bash
cd Laravel
php artisan test
```

### Frontend Testing
```bash
# Student Portal
cd Student-Portal
ng test

# OrgAdmin Portal
cd OrgAdmin-Portal
npm test

# MIS Portal
cd MIS-Portal
npm test
```

## Troubleshooting

### Common Issues

**CORS Errors**: Check that frontend URLs are in Laravel's CORS configuration

**API Connection Failed**: Verify Laravel backend is running on port 8000

**Database Connection Error**: Check MySQL credentials in Laravel `.env`

**Port Already in Use**: Change port in respective configuration files

## License

Proprietary - Leytre Normal University

## Contact

For questions or support, contact the LNU IT Department.

---

**Last Updated**: November 2025
**Version**: 1.0.0
