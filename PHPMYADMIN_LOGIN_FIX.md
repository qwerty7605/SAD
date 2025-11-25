# phpMyAdmin Login Fix

## Issue
Cannot login with `lnu_user` and `lnu_password` credentials.

## Root Cause
phpMyAdmin by default requires configuration to allow arbitrary users to login. Without `PMA_ARBITRARY: 1`, phpMyAdmin restricts which users can login.

## Solution
Updated docker-compose.yml to add `PMA_ARBITRARY: 1` which allows any MySQL user to login through phpMyAdmin.

## How to Apply the Fix

### Step 1: Restart phpMyAdmin with New Configuration

```bash
# Restart just phpMyAdmin container
docker compose restart phpmyadmin

# Or if that doesn't work, recreate it:
docker compose up -d --force-recreate phpmyadmin
```

### Step 2: Try Login Again

Go to: http://localhost:8080

**Login Options:**

**Option 1 - Root User:**
- **Username:** `root`
- **Password:** `root`
- **Server:** `mysql` (or leave default)

**Option 2 - Application User:**
- **Username:** `lnu_user`
- **Password:** `lnu_password`
- **Server:** `mysql` (or leave default)

## What Changed

Added these environment variables to phpMyAdmin service:
- `PMA_ARBITRARY: 1` - Allows any MySQL user to login
- `PMA_USER: ""` - Empty (no default user)
- `PMA_PASSWORD: ""` - Empty (no default password)

## Verification

The `lnu_user` can successfully connect to MySQL directly:
```bash
docker compose exec mysql mysql -u lnu_user -plnu_password lnu_clearance -e "SELECT 'Success'"
```

So the credentials are correct - it's just a phpMyAdmin configuration issue.

## If Still Not Working

1. **Clear browser cache** for localhost:8080
2. **Try incognito/private window**
3. **Check phpMyAdmin logs:**
   ```bash
   docker compose logs -f phpmyadmin
   ```
4. **Verify MySQL user exists:**
   ```bash
   docker compose exec mysql mysql -u root -proot -e "SELECT User FROM mysql.user WHERE User='lnu_user';"
   ```


