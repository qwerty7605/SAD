# Student Portal - Test Credentials

## Database Seed Data Overview

Your Laravel database has been seeded with **136 students** across 4 year levels. All accounts use the same password for testing purposes.

---

## 🔐 Login Credentials

### All Users Password
**Password:** `password`

---

## 👨‍🎓 Student Accounts

### Login Format Options:

**Option 1: Using Email**
- Email: `{student_number}@lnu.edu.ph`
- Password: `password`
- Example: `2024-00006@lnu.edu.ph` / `password`

**Option 2: Using Student Number** (Recommended for Student Portal)
- Student Number: `{student_number}`
- Password: `password`
- Example: `2024-00006` / `password`

---

## 📋 Sample Student Credentials by Year Level

### Year 1 Students (Batch 2024)
- **Total:** 40 students
- **Student Numbers:** `2024-00006` to `2024-00045`
- **Sections:** AI-11, AI-12, AI-13, AI-14, AI-15

**Sample Accounts:**
| Student Number | Email | Section | Password |
|----------------|-------|---------|----------|
| 2024-00006 | 2024-00006@lnu.edu.ph | AI-11 | password |
| 2024-00007 | 2024-00007@lnu.edu.ph | AI-11 | password |
| 2024-00014 | 2024-00014@lnu.edu.ph | AI-12 | password |
| 2024-00022 | 2024-00022@lnu.edu.ph | AI-13 | password |
| 2024-00030 | 2024-00030@lnu.edu.ph | AI-14 | password |
| 2024-00038 | 2024-00038@lnu.edu.ph | AI-15 | password |

---

### Year 2 Students (Batch 2023)
- **Total:** 40 students
- **Student Numbers:** `2023-00001` to `2023-00040`
- **Sections:** AI-21, AI-22, AI-23, AI-24, AI-25

**Sample Accounts:**
| Student Number | Email | Section | Password |
|----------------|-------|---------|----------|
| 2023-00001 | 2023-00001@lnu.edu.ph | AI-21 | password |
| 2023-00009 | 2023-00009@lnu.edu.ph | AI-22 | password |
| 2023-00017 | 2023-00017@lnu.edu.ph | AI-23 | password |
| 2023-00025 | 2023-00025@lnu.edu.ph | AI-24 | password |
| 2023-00033 | 2023-00033@lnu.edu.ph | AI-25 | password |

---

### Year 3 Students (Batch 2022)
- **Total:** 32 students
- **Student Numbers:** `2022-00001` to `2022-00032`
- **Sections:** AI-31, AI-32, AI-33, AI-34

**Sample Accounts:**
| Student Number | Email | Section | Password |
|----------------|-------|---------|----------|
| 2022-00001 | 2022-00001@lnu.edu.ph | AI-31 | password |
| 2022-00009 | 2022-00009@lnu.edu.ph | AI-32 | password |
| 2022-00017 | 2022-00017@lnu.edu.ph | AI-33 | password |
| 2022-00025 | 2022-00025@lnu.edu.ph | AI-34 | password |

---

### Year 4 Students (Batch 2021)
- **Total:** 24 students
- **Student Numbers:** `2021-00001` to `2021-00024`
- **Sections:** AI-41, AI-42, AI-43

**Sample Accounts:**
| Student Number | Email | Section | Password |
|----------------|-------|---------|----------|
| 2021-00001 | 2021-00001@lnu.edu.ph | AI-41 | password |
| 2021-00009 | 2021-00009@lnu.edu.ph | AI-42 | password |
| 2021-00017 | 2021-00017@lnu.edu.ph | AI-43 | password |

---

## 👥 Other User Types (For Reference)

### System Admin
- **Email:** `admin@lnu.edu.ph`
- **Password:** `password`
- **Portal:** MIS-Portal (port 4202)

### Organization Admins
| Username | Email | Password | Portal |
|----------|-------|----------|--------|
| vpsd_admin | vpsd@lnu.edu.ph | password | OrgAdmin-Portal (4201) |
| librarian | librarian@lnu.edu.ph | password | OrgAdmin-Portal (4201) |
| adviser | adviser@lnu.edu.ph | password | OrgAdmin-Portal (4201) |
| treasurer | treasurer@lnu.edu.ph | password | OrgAdmin-Portal (4201) |

---

## 🧪 How to Test Login

### Step 1: Access Student Portal
```
http://localhost:4200
```

### Step 2: Use Any Student Credentials
Try logging in with:
- **Student Number:** `2024-00006`
- **Password:** `password`

Or:
- **Student Number:** `2023-00001`
- **Password:** `password`

### Step 3: Verify Authentication
✅ **Should Work:**
- Any student number from the ranges above with password `password`
- Example: `2024-00006` / `password`

❌ **Should Fail:**
- Invalid student numbers (e.g., `2024-99999`)
- Wrong passwords (e.g., `2024-00006` / `wrongpassword`)
- Non-student emails in Student Portal (e.g., `admin@lnu.edu.ph`)

---

## 🔍 Student Data Details

All students have:
- **Course:** Bachelor of Science in Information Technology
- **Enrollment Status:** `enrolled`
- **Student Type:** Mix of `regular` and `irregular`
- **Contact Number:** Random 11-digit Philippine mobile numbers
- **Date Enrolled:** Varies by year (2024-07-15, 2023-07-15, etc.)

---

## 🛠️ Quick Test Commands

### Check if Database is Seeded
```bash
cd Laravel
docker compose exec php php artisan tinker
```

Then in tinker:
```php
// Check total users
User::count()  // Should return 141 (1 sys_admin + 4 org_admins + 136 students)

// Check students
Student::count()  // Should return 136

// Check a specific student
Student::where('student_number', '2024-00006')->first()
```

---

## 📝 Notes

- **All passwords are `password`** for testing purposes
- Students can login using either **email** or **student number**
- The Student Portal frontend uses **student number** login by default
- Authentication is handled by Laravel Sanctum with JWT tokens
- Invalid credentials will be rejected with proper error messages

---

**Happy Testing! 🎉**
