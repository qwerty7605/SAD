# Docker Setup Guide - LNU Online Clearance System

Complete Docker setup for running all three Angular portals and Laravel backend together.

## Overview

The system is fully dockerized with the following services:

| Service | Port | URL | Description |
|---------|------|-----|-------------|
| **Student Portal** | 4200 | http://localhost:4200 | Student interface |
| **OrgAdmin Portal** | 4201 | http://localhost:4201 | Organization admin interface |
| **MIS Portal** | 4202 | http://localhost:4202 | MIS admin interface |
| **Laravel API** | 8000 | http://localhost:8000 | Backend REST API |
| **MySQL** | 3306 | localhost:3306 | Database |
| **phpMyAdmin** | 8080 | http://localhost:8080 | Database management |

## Prerequisites

- **Docker** (v20.10+)
- **Docker Compose** (v2.0+)

Check your installation:
```bash
docker --version
docker-compose --version
```

## Quick Start

### Option 1: Run All Services Together (Recommended)

From the root directory:

```bash
# Start all services
docker-compose up

# Start in detached mode (background)
docker-compose up -d

# View logs
docker-compose logs -f

# Stop all services
docker-compose down
```

This will start:
- Student Portal (4200)
- OrgAdmin Portal (4201)
- MIS Portal (4202)
- Laravel Backend (8000)
- MySQL Database (3306)
- phpMyAdmin (8080)

### Option 2: Run Individual Portals

If you only need specific portals:

```bash
# Student Portal only
cd Student-Portal
docker-compose up

# OrgAdmin Portal only
cd OrgAdmin-Portal
docker-compose up

# MIS Portal only
cd MIS-Portal
docker-compose up

# Laravel Backend only
cd Laravel
docker-compose up
```

## Docker Files Structure

Each portal has its own Docker configuration:

```
Web Dev/
├── docker-compose.yml              # Unified - runs all services
├── Student-Portal/
│   ├── Dockerfile                  # Student Portal image
│   ├── docker-compose.yml          # Student Portal standalone
│   └── .dockerignore
├── OrgAdmin-Portal/
│   ├── Dockerfile                  # OrgAdmin Portal image
│   ├── docker-compose.yml          # OrgAdmin Portal standalone
│   └── .dockerignore
├── MIS-Portal/
│   ├── Dockerfile                  # MIS Portal image
│   ├── docker-compose.yml          # MIS Portal standalone
│   └── .dockerignore
└── Laravel/
    ├── Dockerfile                  # Laravel backend image
    └── docker-compose.yml          # Laravel standalone
```

## Detailed Commands

### Build Services

```bash
# Build all services
docker-compose build

# Build specific service
docker-compose build student-portal
docker-compose build orgadmin-portal
docker-compose build mis-portal
docker-compose build laravel

# Force rebuild (no cache)
docker-compose build --no-cache
```

### Start Services

```bash
# Start all services
docker-compose up

# Start specific services
docker-compose up student-portal orgadmin-portal
docker-compose up laravel mysql

# Start in detached mode
docker-compose up -d
```

### Stop Services

```bash
# Stop all services
docker-compose down

# Stop and remove volumes (deletes database data)
docker-compose down -v

# Stop specific service
docker-compose stop student-portal
```

### View Logs

```bash
# All services
docker-compose logs -f

# Specific service
docker-compose logs -f student-portal
docker-compose logs -f laravel
docker-compose logs -f mysql

# Last 100 lines
docker-compose logs --tail=100
```

### Execute Commands Inside Containers

```bash
# Access Student Portal container
docker-compose exec student-portal sh

# Access Laravel container
docker-compose exec laravel bash

# Run npm install in Student Portal
docker-compose exec student-portal npm install

# Run Laravel migrations
docker-compose exec laravel php artisan migrate

# Run Laravel seeder
docker-compose exec laravel php artisan db:seed
```

## Initial Setup

### First Time Setup

1. **Clone/Navigate to Project**
```bash
cd "/home/shzzzki/Documents/Workspace/Web Dev"
```

2. **Build All Images**
```bash
docker-compose build
```

3. **Start All Services**
```bash
docker-compose up -d
```

4. **Setup Laravel**
```bash
# Enter Laravel container
docker-compose exec laravel bash

# Inside container:
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
exit
```

5. **Access the Applications**
- Student Portal: http://localhost:4200
- OrgAdmin Portal: http://localhost:4201
- MIS Portal: http://localhost:4202
- Laravel API: http://localhost:8000
- phpMyAdmin: http://localhost:8080

## Development Workflow

### Making Code Changes

All code changes are **automatically reflected** due to volume mounting:

**Angular Portals:**
- Hot reload enabled
- Changes appear immediately in browser

**Laravel:**
- Changes reflected immediately
- No restart needed for most changes

### Installing Dependencies

**Angular (any portal):**
```bash
docker-compose exec student-portal npm install package-name
docker-compose exec orgadmin-portal npm install package-name
docker-compose exec mis-portal npm install package-name
```

**Laravel:**
```bash
docker-compose exec laravel composer require vendor/package
```

### Database Operations

**Run Migrations:**
```bash
docker-compose exec laravel php artisan migrate
```

**Rollback Migration:**
```bash
docker-compose exec laravel php artisan migrate:rollback
```

**Seed Database:**
```bash
docker-compose exec laravel php artisan db:seed
```

**Fresh Migration + Seed:**
```bash
docker-compose exec laravel php artisan migrate:fresh --seed
```

**Access MySQL CLI:**
```bash
docker-compose exec mysql mysql -u lnu_user -plnu_password lnu_clearance
```

### Restart Services

```bash
# Restart all
docker-compose restart

# Restart specific service
docker-compose restart student-portal
docker-compose restart laravel
```

## Port Mapping Reference

### Frontends (Angular)
- **4200** → Student Portal
- **4201** → OrgAdmin Portal
- **4202** → MIS Portal

### Backend
- **8000** → Laravel API (via Nginx)
- **3306** → MySQL Database
- **8080** → phpMyAdmin

## Environment Variables

### Angular Portals

Each portal reads from `src/environments/environment.ts`:

```typescript
export const environment = {
  production: false,
  apiUrl: 'http://localhost:8000'  // Points to Laravel API
};
```

### Laravel

Configure in `Laravel/.env`:

```env
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=lnu_clearance
DB_USERNAME=lnu_user
DB_PASSWORD=lnu_password

CORS_ALLOWED_ORIGINS=http://localhost:4200,http://localhost:4201,http://localhost:4202
```

## Troubleshooting

### Port Already in Use

```bash
# Check what's using a port (e.g., 4200)
lsof -i :4200

# Kill the process
kill -9 <PID>
```

Or change the port in `docker-compose.yml`:
```yaml
ports:
  - "4300:4200"  # Map host 4300 to container 4200
```

### Container Won't Start

```bash
# Check logs
docker-compose logs student-portal

# Remove and rebuild
docker-compose down
docker-compose build --no-cache
docker-compose up
```

### Database Connection Error

```bash
# Ensure MySQL is running
docker-compose ps

# Check MySQL logs
docker-compose logs mysql

# Restart MySQL
docker-compose restart mysql
```

### Node Modules Issues

```bash
# Remove node_modules and reinstall
docker-compose exec student-portal rm -rf node_modules
docker-compose exec student-portal npm install

# Or rebuild the image
docker-compose build --no-cache student-portal
```

### Permission Issues (Linux)

```bash
# Fix ownership
sudo chown -R $USER:$USER .

# Or use UID/GID in docker-compose
export UID=$(id -u)
export GID=$(id -g)
docker-compose up
```

## Production Deployment

### Build for Production

**Angular Portals:**
```bash
# Inside each portal container
docker-compose exec student-portal ng build --configuration production
docker-compose exec orgadmin-portal ng build --configuration production
docker-compose exec mis-portal ng build --configuration production
```

**Optimized Dockerfile for Production:**
```dockerfile
# Multi-stage build
FROM node:24.9.0 AS build
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY . .
RUN ng build --configuration production

FROM nginx:alpine
COPY --from=build /app/dist /usr/share/nginx/html
EXPOSE 80
CMD ["nginx", "-g", "daemon off;"]
```

## Useful Docker Commands

### Cleanup

```bash
# Remove stopped containers
docker container prune

# Remove unused images
docker image prune

# Remove unused volumes
docker volume prune

# Remove everything (careful!)
docker system prune -a

# Remove project volumes
docker-compose down -v
```

### Monitor Resources

```bash
# Show resource usage
docker stats

# Show specific service
docker stats lnu_student_portal
```

### Inspect Network

```bash
# List networks
docker network ls

# Inspect lnu-network
docker network inspect web-dev_lnu-network
```

## Network Configuration

All services are on the same Docker network (`lnu-network`), allowing them to communicate:

- Portals can access Laravel at `http://laravel:8000`
- Laravel can access MySQL at `mysql:3306`

## Backup and Restore

### Backup Database

```bash
# Create backup
docker-compose exec mysql mysqldump -u lnu_user -plnu_password lnu_clearance > backup.sql

# Or with timestamp
docker-compose exec mysql mysqldump -u lnu_user -plnu_password lnu_clearance > backup_$(date +%Y%m%d_%H%M%S).sql
```

### Restore Database

```bash
# Restore from backup
docker-compose exec -T mysql mysql -u lnu_user -plnu_password lnu_clearance < backup.sql
```

## Summary

### Start Development Environment
```bash
docker-compose up -d
```

### Stop Development Environment
```bash
docker-compose down
```

### View All Running Services
```bash
docker-compose ps
```

### Access Applications
- Student: http://localhost:4200
- OrgAdmin: http://localhost:4201
- MIS: http://localhost:4202
- API: http://localhost:8000/api
- phpMyAdmin: http://localhost:8080

---

**Docker setup complete!** All services are containerized and ready for development.
