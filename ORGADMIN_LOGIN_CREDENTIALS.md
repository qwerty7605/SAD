# OrgAdmin Portal Login Credentials

## Quick Answer: Where to Find Credentials

You need to check **TWO tables** in the database:

1. **`users` table** - Contains login credentials (username, password, email, user_type)
2. **`organization_admins` table** - Contains org admin details (full_name, org_id, position)

---

## Step-by-Step: Finding Credentials in phpMyAdmin

### Step 1: Access phpMyAdmin

1. Go to: http://localhost:8080
2. Login with:
   - **Username**: `root`
   - **Password**: `root`
   - **Server**: `mysql` (or leave default)

### Step 2: Select Database

1. Click on database: **`lnu_clearance`**

### Step 3: Check Users Table

1. Click on table: **`users`**
2. Look for rows where **`user_type` = `'org_admin'`**
3. The important columns are:
   - **`username`** - This is what you use to login
   - **`email`** - Can also be used for login (if backend supports it)
   - **`password_hash`** - Hashed password (you can't see actual password)
   - **`user_type`** - Must be `'org_admin'`
   - **`is_active`** - Must be `true`

### Step 4: Check Organization Admins Table (Optional - for details)

1. Click on table: **`organization_admins`**
2. This shows which organization each admin belongs to
3. Important columns:
   - **`admin_id`** - Links to `users.user_id`
   - **`org_id`** - Which organization this admin manages
   - **`full_name`** - Display name
   - **`position`** - Their position/title

---

## Seeded Organization Admin Credentials

If you've run the database seeders, these credentials should exist:

### Option 1: Vice President for Student Development (VPSD)

**Login Credentials:**
- **Username**: `vpsd_admin`
- **Email**: `vpsd@lnu.edu.ph`
- **Password**: `password`
- **Organization**: VPSD (org_id: 1)
- **Full Name**: Dr. Roberto Garcia

### Option 2: College Chief Librarian (Library)

**Login Credentials:**
- **Username**: `librarian`
- **Email**: `librarian@lnu.edu.ph`
- **Password**: `password`
- **Organization**: LIB (org_id: 2)
- **Full Name**: Ms. Ana Mercado

### Option 3: Academic Organization Adviser

**Login Credentials:**
- **Username**: `adviser`
- **Email**: `adviser@lnu.edu.ph`
- **Password**: `password`
- **Organization**: AOA (org_id: 3)
- **Full Name**: Prof. Carlos Fernandez

### Option 4: Academic Organization Treasurer

**Login Credentials:**
- **Username**: `treasurer`
- **Email**: `treasurer@lnu.edu.ph`
- **Password**: `password`
- **Organization**: AOT (org_id: 4)
- **Full Name**: Mrs. Linda Ramos

---

## SQL Queries to Check Credentials

### Check All Organization Admins

```sql
SELECT 
    u.user_id,
    u.username,
    u.email,
    u.user_type,
    u.is_active,
    oa.full_name,
    oa.position,
    o.org_name,
    o.org_code
FROM users u
JOIN organization_admins oa ON u.user_id = oa.admin_id
JOIN organizations o ON oa.org_id = o.org_id
WHERE u.user_type = 'org_admin'
AND u.is_active = 1;
```

### Check Specific Org Admin by Username

```sql
SELECT 
    u.username,
    u.email,
    u.user_type,
    oa.full_name,
    oa.position,
    o.org_name
FROM users u
JOIN organization_admins oa ON u.user_id = oa.admin_id
JOIN organizations o ON oa.org_id = o.org_id
WHERE u.username = 'librarian';
```

---

## How to Use Credentials to Login

### In OrgAdmin Portal:

1. Go to: http://localhost:4201 (OrgAdmin Portal)
2. Login page will appear
3. Use either:
   - **Username** + **Password**, OR
   - **Email** + **Password**

**Example:**
- Username: `librarian`
- Password: `password`

**OR**

- Email: `librarian@lnu.edu.ph`
- Password: `password`

---

## Verify Seeders Have Been Run

### Check if Users Exist

In phpMyAdmin, run this query:

```sql
SELECT COUNT(*) as count FROM users WHERE user_type = 'org_admin';
```

- If **count = 4** → Seeders have been run ✅
- If **count = 0** → Seeders have NOT been run ❌

### If Seeders Haven't Been Run

Run the Laravel seeders:

```bash
cd Laravel
docker compose exec php php artisan db:seed

# Or if running without docker:
php artisan db:seed
```

This will create:
- 4 organization admins
- 1 system admin
- Organizations
- Academic terms
- Students

---

## Creating a New Org Admin Manually

If you need to create a new org admin:

### Step 1: Create User in `users` table

```sql
INSERT INTO users (username, password_hash, email, user_type, is_active, created_at)
VALUES (
    'new_admin',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- This is 'password' hashed
    'new_admin@lnu.edu.ph',
    'org_admin',
    1,
    NOW()
);
```

**Note:** The password_hash above is the bcrypt hash for `'password'`. For a different password, you need to generate the hash using Laravel or a bcrypt generator.

### Step 2: Create Organization Admin Record

First, find the `org_id` for the organization:

```sql
SELECT org_id, org_name FROM organizations;
```

Then insert into `organization_admins`:

```sql
INSERT INTO organization_admins (admin_id, org_id, position, full_name, is_active, assigned_date)
VALUES (
    LAST_INSERT_ID(), -- Gets the last inserted user_id
    1, -- Change to the org_id you want
    'Your Position Title',
    'Your Full Name',
    1,
    NOW()
);
```

---

## Troubleshooting

### Can't Login?

1. **Check user exists:**
   ```sql
   SELECT * FROM users WHERE username = 'librarian';
   ```

2. **Check user_type is correct:**
   ```sql
   SELECT user_type FROM users WHERE username = 'librarian';
   -- Should return: 'org_admin'
   ```

3. **Check user is active:**
   ```sql
   SELECT is_active FROM users WHERE username = 'librarian';
   -- Should return: 1 (true)
   ```

4. **Check password is correct:**
   - Default seeded password is: `password`
   - Make sure there are no extra spaces or typos

5. **Check backend authentication is working:**
   - Verify Laravel backend is running
   - Check Laravel logs for authentication errors

### Password Not Working?

The password is stored as a **hash** in the database. If you need to reset it:

**Option 1: Use Laravel Tinker (Recommended)**
```bash
cd Laravel
docker compose exec php php artisan tinker
```

Then in tinker:
```php
$user = \App\Models\User::where('username', 'librarian')->first();
$user->password_hash = \Illuminate\Support\Facades\Hash::make('newpassword');
$user->save();
exit;
```

**Option 2: Use SQL with bcrypt hash**
```sql
UPDATE users 
SET password_hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' 
WHERE username = 'librarian';
```
*(This hash is for password: 'password')*

---

## Quick Reference

| Username | Password | Email | Organization |
|----------|----------|-------|--------------|
| `vpsd_admin` | `password` | vpsd@lnu.edu.ph | VPSD |
| `librarian` | `password` | librarian@lnu.edu.ph | Library |
| `adviser` | `password` | adviser@lnu.edu.ph | Academic Org Adviser |
| `treasurer` | `password` | treasurer@lnu.edu.ph | Academic Org Treasurer |

---

## Summary

**To find org admin credentials:**

1. ✅ Open phpMyAdmin: http://localhost:8080
2. ✅ Select database: `lnu_clearance`
3. ✅ Open table: `users`
4. ✅ Filter/search: `user_type = 'org_admin'`
5. ✅ Use `username` and default password: `password`

**Default credentials for seeded users:**
- Username: `librarian`, `adviser`, `treasurer`, or `vpsd_admin`
- Password: `password`


