# How to Use Individual Docker Compose Files (One Per Project)

## Overview

This guide explains how to use **individual docker-compose.yml files** in each project folder, following the "one docker-compose per project" principle.

## Project Structure

```
Web Dev/
├── Laravel/
│   ├── docker-compose.yml          # Laravel + MySQL + phpMyAdmin + Nginx
│   └── Dockerfile
├── Student-Portal/
│   ├── docker-compose.yml          # Student Portal only
│   └── Dockerfile
├── OrgAdmin-Portal/
│   ├── docker-compose.yml          # OrgAdmin Portal only
│   └── Dockerfile
└── MIS-Portal/
    ├── docker-compose.yml          # MIS Portal only
    └── Dockerfile
```

## Step-by-Step: Starting All Services

### Step 1: Stop Root Docker Compose (If Running)

```bash
# Stop root docker-compose first to avoid port conflicts
cd "/home/shzzzki/Documents/Workspace/Web Dev"
docker compose down
```

### Step 2: Start Laravel Backend (First - Required)

Laravel backend provides MySQL database and API that all frontends need.

```bash
cd Laravel
docker compose up -d

# Check status
docker compose ps

# View logs
docker compose logs -f
```

**This starts:**
- ✅ Laravel PHP application
- ✅ MySQL database (port 3306)
- ✅ phpMyAdmin (port 8080)
- ✅ Nginx (port 8000 or 80)

**Wait for MySQL to be ready** (30 seconds):
```bash
docker compose logs mysql | grep "ready for connections"
```

### Step 3: Start Frontend Portals (In Any Order)

**Terminal 1 - Student Portal:**
```bash
cd Student-Portal
docker compose up -d

# Or see logs
docker compose up
```

**Terminal 2 - OrgAdmin Portal:**
```bash
cd OrgAdmin-Portal
docker compose up -d

# Or see logs
docker compose up
```

**Terminal 3 - MIS Portal:**
```bash
cd MIS-Portal
docker compose up -d

# Or see logs
docker compose up
```

**Or start all frontends in one go:**
```bash
cd Student-Portal && docker compose up -d && cd ..
cd OrgAdmin-Portal && docker compose up -d && cd ..
cd MIS-Portal && docker compose up -d && cd ..
```

### Step 4: Verify All Services Are Running

```bash
# Check Laravel services
cd Laravel
docker compose ps

# Check Student Portal
cd ../Student-Portal
docker compose ps

# Check OrgAdmin Portal
cd ../OrgAdmin-Portal
docker compose ps

# Check MIS Portal
cd ../MIS-Portal
docker compose ps
```

**Or check all at once:**
```bash
docker ps
```

## Access Services

Once all services are running:

- **Laravel API**: http://localhost:8000 (or check Laravel/docker-compose.yml)
- **Student Portal**: http://localhost:4200
- **OrgAdmin Portal**: http://localhost:4201
- **MIS Portal**: http://localhost:4202
- **phpMyAdmin**: http://localhost:8080

## Stopping Services

### Stop All Services

```bash
# Stop each service individually
cd Laravel && docker compose down && cd ..
cd Student-Portal && docker compose down && cd ..
cd OrgAdmin-Portal && docker compose down && cd ..
cd MIS-Portal && docker compose down && cd ..
```

### Stop Individual Services

```bash
# Stop only Laravel
cd Laravel
docker compose down

# Stop only Student Portal
cd Student-Portal
docker compose down

# etc...
```

## Common Workflows

### Workflow 1: Full Stack Development (All Services)

```bash
# 1. Start Laravel backend
cd Laravel
docker compose up -d

# 2. Start all frontends
cd ../Student-Portal && docker compose up -d && cd ..
cd ../OrgAdmin-Portal && docker compose up -d && cd ..
cd ../MIS-Portal && docker compose up -d && cd ..
```

### Workflow 2: Work on One Portal Only

```bash
# 1. Start Laravel backend (required for API)
cd Laravel
docker compose up -d

# 2. Start only the portal you're working on
cd ../OrgAdmin-Portal
docker compose up
```

### Workflow 3: Work on Backend Only

```bash
# Start only Laravel backend
cd Laravel
docker compose up
```

## Important Configuration Notes

### 1. Angular Portals Must Connect to Laravel Backend

**In each portal's `src/environments/environment.ts`:**
```typescript
export const environment = {
  production: false,
  apiUrl: 'http://localhost:8000'  // Laravel backend URL
};
```

### 2. Laravel Backend Exposes MySQL

**In Laravel/docker-compose.yml:**
```yaml
mysql:
  ports:
    - "3306:3306"  # Exposed to host so frontends can connect
```

This allows Angular portals (running separately) to connect to the Laravel API at `http://localhost:8000`.

### 3. Port Assignments

| Service | Port | Why |
|---------|------|-----|
| Laravel/Nginx | 8000 (or 80) | Backend API |
| MySQL | 3306 | Database |
| phpMyAdmin | 8080 | Database GUI |
| Student Portal | 4200 | Frontend |
| OrgAdmin Portal | 4201 | Frontend |
| MIS Portal | 4202 | Frontend |

**Important:** Make sure ports don't conflict!

## Troubleshooting

### Port Already in Use

If you get "port is already allocated" error:

```bash
# Find what's using the port
sudo lsof -i :4200  # For Student Portal
sudo lsof -i :8000  # For Laravel

# Stop the conflicting service
cd Laravel
docker compose down

# Or stop specific service
cd Student-Portal
docker compose down
```

### Services Can't Connect to Laravel Backend

**Check:**
1. Laravel backend is running:
   ```bash
   cd Laravel
   docker compose ps
   ```

2. Laravel backend is accessible:
   ```bash
   curl http://localhost:8000
   ```

3. Angular portal's `apiUrl` is correct:
   ```typescript
   // src/environments/environment.ts
   apiUrl: 'http://localhost:8000'
   ```

### Database Connection Issues

**Check MySQL is running:**
```bash
cd Laravel
docker compose ps mysql

# Check MySQL logs
docker compose logs mysql
```

**Verify MySQL credentials in Laravel/.env:**
```env
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=lnu_clearance
DB_USERNAME=lnu_user
DB_PASSWORD=lnu_password
```

## Advantages of Individual Docker Compose

✅ **One compose per project** - follows best practices  
✅ **Independent services** - can run/stop services individually  
✅ **Clear boundaries** - each project manages its own dependencies  
✅ **Flexible** - run only what you need  
✅ **Easier debugging** - isolate issues to one service  
✅ **Production-ready** - matches deployment architecture  

## Quick Reference

### Start Everything
```bash
cd Laravel && docker compose up -d && cd ..
cd Student-Portal && docker compose up -d && cd ..
cd OrgAdmin-Portal && docker compose up -d && cd ..
cd MIS-Portal && docker compose up -d && cd ..
```

### Stop Everything
```bash
cd Laravel && docker compose down && cd ..
cd Student-Portal && docker compose down && cd ..
cd OrgAdmin-Portal && docker compose down && cd ..
cd MIS-Portal && docker compose down && cd ..
```

### View Logs
```bash
# Laravel logs
cd Laravel
docker compose logs -f

# Specific portal logs
cd OrgAdmin-Portal
docker compose logs -f
```

---

**This approach follows the "one docker-compose per project" principle you were taught!**


