# Docker Architecture Decision - One Compose Per Project vs Root Compose

## The Question

You asked: **"Why the need for the whole docker compose though? We were taught one docker compose per project. Should we remove the whole docker compose?"**

This is an **excellent architectural question**! Let me explain both approaches and help you decide.

---

## Two Approaches Explained

### Approach 1: Individual Docker Compose Files (What You Learned) ✅

**Structure:**
```
Web Dev/
├── Student-Portal/
│   └── docker-compose.yml    # Only Student Portal services
├── OrgAdmin-Portal/
│   └── docker-compose.yml    # Only OrgAdmin Portal services
├── MIS-Portal/
│   └── docker-compose.yml    # Only MIS Portal services
└── Laravel/
    └── docker-compose.yml    # Laravel + MySQL + phpMyAdmin
```

**How it works:**
```bash
# Start Laravel backend (includes MySQL, phpMyAdmin, nginx)
cd Laravel
docker compose up -d

# Start Student Portal
cd ../Student-Portal
docker compose up -d

# Start OrgAdmin Portal
cd ../OrgAdmin-Portal
docker compose up -d

# Start MIS Portal
cd ../MIS-Portal
docker compose up -d
```

**Pros:**
- ✅ **One docker-compose per project** (follows best practices)
- ✅ **Each project is independent** (proper separation)
- ✅ **Can run only what you need** (run just one portal if testing)
- ✅ **Clear project boundaries** (easier to understand)
- ✅ **Follows microservices pattern**
- ✅ **Easier to maintain** (changes to one project don't affect others)

**Cons:**
- ❌ Need to manually start multiple services
- ❌ Need to coordinate startup order (Laravel before portals)
- ❌ More terminal windows/commands

---

### Approach 2: Root Docker Compose (Current Setup)

**Structure:**
```
Web Dev/
└── docker-compose.yml    # Runs ALL services together
```

**How it works:**
```bash
# Start everything with one command
cd "/home/shzzzki/Documents/Workspace/Web Dev"
docker compose up -d
```

**Pros:**
- ✅ **Single command** (faster to start everything)
- ✅ **Coordinated startup** (Laravel starts before portals automatically)
- ✅ **Shared network** (services can communicate easily)
- ✅ **Development convenience** (good when working on full stack)

**Cons:**
- ❌ **Violates "one compose per project" principle**
- ❌ **All-or-nothing** (can't easily run just one portal)
- ❌ **Less modular** (all services in one file)
- ❌ **Tighter coupling** between projects

---

## My Recommendation: Use Individual Docker Compose Files ✅

Based on what you were taught and best practices, **I recommend using individual docker-compose files per project**.

### Why?

1. **Follows Best Practices**
   - "One docker-compose per project" is the standard pattern
   - Better for microservices architecture
   - Each project manages its own dependencies

2. **Better Organization**
   - Clear project boundaries
   - Each team/developer can work on one service independently
   - Easier to understand project structure

3. **More Flexible**
   - Run only what you need (e.g., just OrgAdmin Portal for testing)
   - Don't waste resources running services you're not using
   - Easier debugging (isolate issues to one service)

4. **Production Ready**
   - Matches how services would be deployed separately
   - Easier to scale individual services
   - Better for CI/CD pipelines

---

## Recommended Structure (Individual Docker Compose)

### Laravel Project (`Laravel/docker-compose.yml`)
**Contains:**
- Laravel PHP application
- MySQL database
- phpMyAdmin
- Nginx

**Why together?** Backend services that work together.

### Frontend Projects (Each has its own)

**Student-Portal/docker-compose.yml:**
- Student Portal Angular app
- Node.js/NPM

**OrgAdmin-Portal/docker-compose.yml:**
- OrgAdmin Portal Angular app
- Node.js/NPM

**MIS-Portal/docker-compose.yml:**
- MIS Portal Angular app
- Node.js/NPM

**Why separate?** Each frontend is independent and connects to the same Laravel backend.

---

## How to Use Individual Docker Compose Files

### Step 1: Stop Root Docker Compose (If Running)

```bash
cd "/home/shzzzki/Documents/Workspace/Web Dev"
docker compose down
```

### Step 2: Start Laravel Backend First

```bash
cd Laravel
docker compose up -d

# Verify it's running
docker compose ps
```

### Step 3: Start Each Frontend Portal

```bash
# Terminal 1: Student Portal
cd Student-Portal
docker compose up

# Terminal 2: OrgAdmin Portal
cd OrgAdmin-Portal
docker compose up

# Terminal 3: MIS Portal
cd MIS-Portal
docker compose up
```

Or run them all in detached mode:
```bash
cd Student-Portal && docker compose up -d && cd ..
cd OrgAdmin-Portal && docker compose up -d && cd ..
cd MIS-Portal && docker compose up -d && cd ..
```

### Step 4: Access Services

- Laravel API: http://localhost:8000 (or check Laravel/docker-compose.yml for port)
- Student Portal: http://localhost:4200
- OrgAdmin Portal: http://localhost:4201
- MIS Portal: http://localhost:4202
- phpMyAdmin: http://localhost:8080

---

## Decision: Should You Remove Root Docker Compose?

### Option A: Keep Both (Flexibility) ✅ Recommended

**Keep individual docker-compose.yml files as primary** (one per project)

**Keep root docker-compose.yml as optional convenience** (for quick full-stack testing)

**Rename root file** to indicate it's optional:
```bash
# Rename to make it clear it's optional
mv docker-compose.yml docker-compose.all.yml
```

**Usage:**
- **Development:** Use individual docker-compose files
- **Quick Testing:** Use `docker compose -f docker-compose.all.yml up` when you need everything

### Option B: Remove Root Docker Compose (Clean) ✅ Also Valid

If you want strict adherence to "one compose per project":

```bash
# Backup first (just in case)
cp docker-compose.yml docker-compose.all.yml.backup

# Remove root docker-compose
rm docker-compose.yml
```

**Pros:**
- Clean project structure
- Forces proper project boundaries
- No confusion about which compose file to use

**Cons:**
- No convenience "start everything" command
- Need to manually start each service

---

## Configuration Needed for Individual Docker Compose

### Important: Port Conflicts

When using individual docker-compose files, you need to ensure:

1. **Only ONE MySQL instance** (from Laravel/docker-compose.yml)
2. **Only ONE phpMyAdmin** (from Laravel/docker-compose.yml)
3. **Different ports for each frontend** (4200, 4201, 4202)

### Network Configuration

Each frontend needs to connect to Laravel backend:

**In Angular environments (each portal):**
```typescript
// src/environments/environment.ts
export const environment = {
  production: false,
  apiUrl: 'http://localhost:8000'  // Laravel backend
};
```

**In Laravel docker-compose.yml:**
```yaml
services:
  mysql:
    ports:
      - "3306:3306"  # Expose to host
```

This allows frontend services (running separately) to connect to the Laravel backend running in its own docker-compose.

---

## Summary

### Recommended Approach:

✅ **Use Individual Docker Compose Files** (one per project)
- Laravel/docker-compose.yml
- Student-Portal/docker-compose.yml
- OrgAdmin-Portal/docker-compose.yml
- MIS-Portal/docker-compose.yml

❌ **Root docker-compose.yml is Optional**
- Keep it for convenience (rename to docker-compose.all.yml)
- OR remove it if you want strict project separation

### Why Individual is Better:

1. ✅ Follows "one compose per project" principle (what you learned)
2. ✅ Better organization and separation of concerns
3. ✅ More flexible (run only what you need)
4. ✅ Production-ready architecture
5. ✅ Easier to maintain and debug

---

## Next Steps

**Option 1: Keep Both (Recommended)**
```bash
# Rename root compose to indicate it's optional
mv docker-compose.yml docker-compose.all.yml

# Use individual composes for regular development
cd Laravel && docker compose up -d
cd ../OrgAdmin-Portal && docker compose up -d
# etc...

# Use root compose only when needed
docker compose -f docker-compose.all.yml up -d
```

**Option 2: Remove Root Compose**
```bash
# Backup first
cp docker-compose.yml docker-compose.all.yml.backup

# Remove root compose
rm docker-compose.yml

# Use only individual composes
cd Laravel && docker compose up -d
cd ../OrgAdmin-Portal && docker compose up -d
# etc...
```

---

**Your choice!** Both approaches work, but **individual docker-compose files are more aligned with best practices and what you were taught.**


