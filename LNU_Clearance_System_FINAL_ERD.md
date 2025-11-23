==============================================================
LEYTE NORMAL UNIVERSITY - CLEARANCE AUTOMATION SYSTEM
FINALIZED DATABASE DESIGN & SYSTEM WORKFLOW
==============================================================

IMPORTANT TERMINOLOGY NOTE:
---------------------------
In this documentation, "ORGANIZATIONS" refers to PERMANENT ADMINISTRATIVE 
OFFICES/DEPARTMENTS of the university that issue clearances, such as:
   - Academic Organization Treasurer
   - Academic Organization Adviser
   - Vice President for Student Development 
   - College Chief Librarian.

These are NOT student clubs or student organizations.
These are permanent institutional departments that exist year-round.

==============================================================

SYSTEM OVERVIEW
----------------
The LNU Clearance Automation System digitalizes the student clearance process by:
- Eliminating paper-based clearance forms
- Providing real-time clearance status visibility for students
- Enabling organization signatories to approve students with simple check/approve actions
- Automating overall clearance status calculations
- Integrating with existing student POES website

KEY PRINCIPLES:
- Students see only status (Approved/Pending/Needs Compliance) - NO detailed requirement info online
- Compliance details must be discussed in person at the department/office
- Each department/office has ONE designated signatory who can approve
- When signatory approves, status automatically updates - no manual admin intervention
- Administrative offices (DIGITS, Library, and etc.) are PERMANENT - always configured in system
- These are NOT student clubs/orgs - they are permanent university departments/offices
- Clearances auto-generate when students enroll for a new term

==============================================================
DATABASE ENTITY RELATIONSHIP DIAGRAM (ERD)
==============================================================

ENTITIES AND ATTRIBUTES:
------------------------

1. USERS (Base Authentication Table)
   --------------------------------------
   - user_id (PK, INT, AUTO_INCREMENT)
   - username (VARCHAR(50), UNIQUE, NOT NULL)
   - password_hash (VARCHAR(255), NOT NULL)
   - email (VARCHAR(100), UNIQUE, NOT NULL)
   - user_type (ENUM: 'student', 'org_admin', 'sys_admin')
   - created_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)
   - last_login (TIMESTAMP, NULL)
   - is_active (BOOLEAN, DEFAULT TRUE)
   
   PURPOSE: Central authentication for all system users
   
   RELATIONSHIPS:
   - One-to-One with STUDENTS (when user_type = 'student')
   - One-to-One with ORGANIZATION_ADMINS (when user_type = 'org_admin')
   - One-to-One with SYSTEM_ADMINS (when user_type = 'sys_admin')


2. STUDENTS (Low-Level Access - View Only)
   --------------------------------------
   - student_id (PK, FK → USERS.user_id)
   - student_number (VARCHAR(20), UNIQUE, NOT NULL)
   - first_name (VARCHAR(50), NOT NULL)
   - middle_name (VARCHAR(50), NULL)
   - last_name (VARCHAR(50), NOT NULL)
   - course (VARCHAR(100), NULL)
   - year_level (INT, NULL)
   - section (VARCHAR(20), NULL)
   - contact_number (VARCHAR(20), NULL)
   - date_enrolled (DATE, NULL)
   - enrollment_status (ENUM: 'enrolled', 'inactive', 'graduated', 'withdrawn')
   
   PURPOSE: Student profile information
   
   RELATIONSHIPS:
   - Extends USERS table (FK to user_id)
   - One-to-Many with STUDENT_CLEARANCES


3. ORGANIZATIONS (Permanent Administrative Departments/Offices)
   --------------------------------------
   - org_id (PK, INT, AUTO_INCREMENT)
   - org_code (VARCHAR(20), UNIQUE, NOT NULL) -- e.g., 'DIGITS', 'LIB', 'REG'
   - org_name (VARCHAR(100), NOT NULL) -- e.g., 'IT Department', 'Library'
   - org_type (ENUM: 'academic', 'administrative', 'finance', 'student_services')
   - department (VARCHAR(100), NULL)
   - is_active (BOOLEAN, DEFAULT TRUE) -- Can temporarily disable without deleting
   - requires_clearance (BOOLEAN, DEFAULT TRUE) -- Whether this office issues clearances
   - created_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)
   
   PURPOSE: PERMANENT university administrative offices/departments that issue clearances
   EXAMPLES: 
   - Academic Organization Treasurer
   - Academic Organization Adviser 
   - Vice President for Student Development
   - College Chief Librarian
   
   NOTE: These are NOT student clubs/organizations - they are permanent institutional 
   departments that exist year-round and always participate in the clearance process.
   They never need to be "activated" per term - they are ALWAYS active.
   
   RELATIONSHIPS:
   - One-to-One with ORGANIZATION_ADMINS (one signatory per office)
   - One-to-Many with CLEARANCE_ITEMS


4. ORGANIZATION_ADMINS (Office Signatories - Mid-Level Access)
   --------------------------------------
   - admin_id (PK, FK → USERS.user_id)
   - org_id (FK → ORGANIZATIONS.org_id, UNIQUE) -- Each office has ONE signatory
   - position (VARCHAR(100), NULL) -- e.g., "Librarian", "IT Department Head"
   - full_name (VARCHAR(150), NOT NULL) -- Display name for "Approved by"
   - assigned_date (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)
   - removed_date (TIMESTAMP, NULL) -- When signatory is replaced
   - is_active (BOOLEAN, DEFAULT TRUE)
   
   PURPOSE: One designated signatory per administrative office who can approve clearances
   EXAMPLES:
   - Librarian for Library
   - IT Department Head for DIGITS
   - Finance Officer for Finance Office
   - Registrar for Registrar's Office
   
   NOTE: UNIQUE constraint on org_id ensures only ONE active signatory per office
   This is the person whose name appears as "Approved by: [Name]" on clearances
   
   RELATIONSHIPS:
   - Extends USERS table (FK to user_id)
   - One-to-One with ORGANIZATIONS (one signatory per office)
   - One-to-Many with CLEARANCE_ITEMS (as approver)


5. SYSTEM_ADMINS (High-Level Access - Full Control)
   --------------------------------------
   - sys_admin_id (PK, FK → USERS.user_id)
   - admin_level (ENUM: 'super_admin', 'mis_staff')
   - full_name (VARCHAR(150), NOT NULL)
   - department (VARCHAR(100), NULL)
   - assigned_date (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)
   
   PURPOSE: MIS staff who manage the entire system
   
   RELATIONSHIPS:
   - Extends USERS table (FK to user_id)
   - Has system-wide access to all tables


6. ACADEMIC_TERMS (Semester/Term Information)
   --------------------------------------
   - term_id (PK, INT, AUTO_INCREMENT)
   - academic_year (VARCHAR(20), NOT NULL) -- e.g., "2024-2025"
   - semester (ENUM: 'first', 'second', 'summer')
   - term_name (VARCHAR(50), NOT NULL) -- e.g., "First Semester 2024-2025"
   - start_date (DATE, NOT NULL)
   - end_date (DATE, NOT NULL)
   - enrollment_start (DATE, NOT NULL)
   - enrollment_end (DATE, NOT NULL)
   - is_current (BOOLEAN, DEFAULT FALSE)
   - clearance_deadline (DATE, NULL) -- When clearances must be complete
   - created_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)
   
   PURPOSE: Academic term/semester configuration
   NOTE: Only ONE term can have is_current = TRUE at a time
   
   RELATIONSHIPS:
   - One-to-Many with STUDENT_CLEARANCES


7. STUDENT_CLEARANCES (Main Clearance Record per Student per Term)
   --------------------------------------
   - clearance_id (PK, INT, AUTO_INCREMENT)
   - student_id (FK → STUDENTS.student_id, NOT NULL)
   - term_id (FK → ACADEMIC_TERMS.term_id, NOT NULL)
   - overall_status (ENUM: 'approved', 'pending', 'incomplete')
   - created_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)
   - last_updated (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP)
   - approved_date (TIMESTAMP, NULL) -- When overall_status became 'approved'
   - is_locked (BOOLEAN, DEFAULT FALSE) -- Prevents changes after enrollment
   
   PURPOSE: One clearance record per student per term
   
   BUSINESS RULES:
   - overall_status = 'approved' when ALL clearance items are 'approved'
   - overall_status = 'pending' when some items are pending
   - overall_status = 'incomplete' when any item is 'needs_compliance'
   - Auto-updates when any CLEARANCE_ITEMS status changes
   
   RELATIONSHIPS:
   - Many-to-One with STUDENTS
   - Many-to-One with ACADEMIC_TERMS
   - One-to-Many with CLEARANCE_ITEMS
   - UNIQUE constraint on (student_id, term_id) -- One clearance per student per term


8. CLEARANCE_ITEMS (Individual Organization Clearance Status)
   --------------------------------------
   - item_id (PK, INT, AUTO_INCREMENT)
   - clearance_id (FK → STUDENT_CLEARANCES.clearance_id, NOT NULL)
   - org_id (FK → ORGANIZATIONS.org_id, NOT NULL)
   - status (ENUM: 'approved', 'pending', 'needs_compliance')
   - approved_by (FK → ORGANIZATION_ADMINS.admin_id, NULL) -- Who approved this
   - approved_date (TIMESTAMP, NULL) -- When it was approved
   - is_auto_approved (BOOLEAN, DEFAULT FALSE) -- Was it auto-approved?
   - created_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)
   - status_updated (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP)
   
   PURPOSE: Individual clearance status for each organization
   
   STATUS MEANINGS:
   - 'approved' = Student has no pending requirements with this org
   - 'pending' = Default status, awaiting org admin review
   - 'needs_compliance' = Student must visit office to resolve issues
   
   BUSINESS RULES:
   - Auto-generated as 'approved' if student has no pending requirements
   - Auto-generated as 'pending' if requirements need verification
   - Office signatory changes 'pending' → 'approved' with simple check action
   - Office signatory changes 'approved' → 'needs_compliance' if issues found
   - When status changes to 'approved', system auto-fills approved_by and approved_date
   - Triggers update of parent STUDENT_CLEARANCES.overall_status
   
   RELATIONSHIPS:
   - Many-to-One with STUDENT_CLEARANCES
   - Many-to-One with ORGANIZATIONS
   - Many-to-One with ORGANIZATION_ADMINS (as approver)
   - UNIQUE constraint on (clearance_id, org_id) -- One item per org per clearance


9. AUDIT_LOGS (System Activity Tracking)
   --------------------------------------
   - log_id (PK, INT, AUTO_INCREMENT)
   - user_id (FK → USERS.user_id, NULL)
   - action_type (ENUM: 'create', 'update', 'delete', 'login', 'logout')
   - table_name (VARCHAR(50), NULL)
   - record_id (INT, NULL)
   - old_value (TEXT, NULL)
   - new_value (TEXT, NULL)
   - ip_address (VARCHAR(45), NULL)
   - user_agent (VARCHAR(255), NULL)
   - created_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)
   
   PURPOSE: Complete audit trail of all system changes
   CRITICAL FOR: Security, compliance, troubleshooting, admin accountability


==============================================================
ENTITY RELATIONSHIP SUMMARY
==============================================================

USERS (1) ←→ (1) STUDENTS
USERS (1) ←→ (1) ORGANIZATION_ADMINS
USERS (1) ←→ (1) SYSTEM_ADMINS

STUDENTS (1) ←→ (∞) STUDENT_CLEARANCES
ACADEMIC_TERMS (1) ←→ (∞) STUDENT_CLEARANCES

STUDENT_CLEARANCES (1) ←→ (∞) CLEARANCE_ITEMS
ORGANIZATIONS (1) ←→ (∞) CLEARANCE_ITEMS
ORGANIZATION_ADMINS (1) ←→ (∞) CLEARANCE_ITEMS (as approver)

ORGANIZATIONS (1) ←→ (1) ORGANIZATION_ADMINS


==============================================================
SIMPLIFIED SYSTEM WORKFLOW
==============================================================

PHASE 1: SYSTEM INITIALIZATION (One-Time Setup)
------------------------------------------------
1. System Admin adds all permanent administrative offices to the system
   - Add DIGITS (IT Department), Library, Academic Organization Treasurer, Academic Organization Adviser
   - These are PERMANENT university departments that always exist
   - Set requires_clearance = TRUE for all offices that issue clearances
   
   EXAMPLE ORGANIZATIONS TO ADD:
   * Vice President for Student Development
   * College Chief Librarian
   * Academic Organization Adviser
   * Academic Organization Treasurer
   * Above is the 4 things that signs our clearance.

2. System Admin assigns one signatory per office
   - One designated person per administrative office
   - E.g., Head Librarian for Library, IT Director for DIGITS
   - This person has authority to approve clearances for their office

3. System Admin creates Academic Term
   - Define term dates, enrollment period, clearance deadline
   - Set is_current = TRUE

NOTE: Once set up, these offices remain in the system permanently.
No need to "activate" or "deactivate" them each semester - they're always active.


PHASE 2: AUTOMATIC CLEARANCE GENERATION (Per Term)
---------------------------------------------------
TRIGGER: When student enrolls for new term
OR: Batch process runs at enrollment_start date

FOR EACH enrolled student:
   
   Step 1: Create STUDENT_CLEARANCES record
   - student_id = current student
   - term_id = current term
   - overall_status = 'pending' (default)
   - created_at = now

   Step 2: For EACH permanent administrative office (where requires_clearance = TRUE):
      Create CLEARANCE_ITEMS record:
      
      NOTE: System checks if student has pending requirements with each office
      (This could be integrated with finance system, library system, etc.)
      
      IF student has NO pending requirements with this office:
         - status = 'approved'
         - is_auto_approved = TRUE
         - approved_by = NULL (auto-approved)
         - approved_date = NOW
      
      ELSE:
         - status = 'pending' (default, awaiting signatory review)
         - is_auto_approved = FALSE
         - approved_by = NULL
         - approved_date = NULL

   Step 3: Calculate overall_status
      IF ALL clearance items = 'approved':
         - overall_status = 'approved'
         - approved_date = NOW
      ELSE IF ANY clearance item = 'needs_compliance':
         - overall_status = 'incomplete'
      ELSE:
         - overall_status = 'pending'

IMPORTANT: Clearances are generated for ALL permanent offices automatically.
No manual "activation" needed - if the office exists and requires_clearance = TRUE,
clearances are automatically created for all students.


PHASE 3: STUDENT VIEW (Read-Only Access)
-----------------------------------------
Student logs into POES website and sees:

1. QUICK STATUS INDICATOR (on main dashboard)
   - "Clearance Status: ✅ APPROVED" (green)
   - OR "Clearance Status: ⚠️ PENDING" (yellow)
   - OR "Clearance Status: ❌ INCOMPLETE" (red)

2. DETAILED CLEARANCE PAGE (separate page from nav menu)
   Shows table/list of all administrative offices:
   
   | Office/Department | Status              | Signatory Name  |
   |-------------------|---------------------|-----------------|
   | Library           | ✅ Approved         | Ms. Jane Doe    |
   | DIGITS (IT Dept)  | ⏳ Pending          | Mr. John Smith  |
   | Finance Office    | ❌ Needs Compliance | Ms. Mary Jones  |
   | Registrar         | ✅ Approved         | Mr. Bob Lee     |

   STATUS MEANINGS (shown to student):
   - ✅ Approved = No action needed for this office
   - ⏳ Pending = Being reviewed by office, check back later
   - ❌ Needs Compliance = Visit this office PERSONALLY to resolve

   FOR "Needs Compliance" items:
   - Message: "Please visit [Office Name] to discuss and settle your requirements."
   - NO DETAILED REQUIREMENT INFO SHOWN ONLINE
   - Student must go to the physical office to find out what they need


PHASE 4: OFFICE SIGNATORY WORKFLOW (Mid-Level Access)
------------------------------------------------------
Office Signatory logs into their ADMIN PORTAL and sees:

1. LIST OF ALL STUDENTS needing clearance for current term
   Filtered to show only THEIR office's clearances
   
   Example: The Librarian only sees Library clearance statuses
           The IT Director (DIGITS) only sees DIGITS clearance statuses

   View options:
   - All students
   - Pending only
   - Needs compliance only
   - Search by student number/name

2. FOR EACH STUDENT, they see:
   - Student Number
   - Student Name
   - Current Status (Approved/Pending/Needs Compliance)
   - Simple ACTION BUTTONS

3. APPROVAL PROCESS (Simple Check/Approve):
   
   Scenario A: Student has NO issues with this office
   - Signatory clicks "APPROVE" button
   - System automatically:
     * Changes status = 'approved'
     * Sets approved_by = current signatory's admin_id
     * Sets approved_date = NOW
     * Updates STUDENT_CLEARANCES.overall_status
     * NO manual status update needed!

   Scenario B: Student has issues/lackings with this office
   - Signatory clicks "NEEDS COMPLIANCE" button
   - System automatically:
     * Changes status = 'needs_compliance'
     * Sets status_updated = NOW
     * Updates STUDENT_CLEARANCES.overall_status
   - Student must visit office to find out what they need to comply

   Scenario C: Signatory wants to REVERT approval
   - Signatory can change 'approved' back to 'pending' or 'needs_compliance'
   - System logs the change in AUDIT_LOGS for accountability

4. BATCH APPROVE FEATURE (Optional but recommended):
   - Signatory can select multiple students
   - Click "Approve All Selected"
   - System processes all at once
   - Useful when clearing large batches of students without issues


PHASE 5: AUTOMATIC STATUS CALCULATION
--------------------------------------
TRIGGERED: Whenever ANY clearance item status changes

System automatically recalculates STUDENT_CLEARANCES.overall_status:

IF all CLEARANCE_ITEMS.status = 'approved':
   overall_status = 'approved'
   approved_date = NOW
   
ELSE IF any CLEARANCE_ITEMS.status = 'needs_compliance':
   overall_status = 'incomplete'
   approved_date = NULL
   
ELSE:
   overall_status = 'pending'
   approved_date = NULL


PHASE 6: ENROLLMENT DAY
-----------------------
1. Student arrives at enrollment venue with ID only (no paper clearance)

2. MIS staff opens enrollment system and checks clearance status:
   
   IF overall_status = 'approved':
      - ✅ Proceed with enrollment
      - Allow course registration
      - Generate Certificate of Registration
   
   ELSE:
      - ❌ Cannot enroll
      - Show which organizations still need approval
      - Student must complete clearance first
      - Come back when status = 'approved'

3. OPTIONAL: Lock clearance after enrollment
   - Set is_locked = TRUE
   - Prevents further changes
   - Maintains enrollment record integrity


==============================================================
USER ACCESS LEVELS & PERMISSIONS MATRIX
==============================================================

LOW-LEVEL ACCESS (STUDENTS)
----------------------------
CAN:
- View own clearance status (overall + per organization)
- View organization contact information
- View signatory names
- View office locations and hours

CANNOT:
- Modify any clearance status
- View other students' clearances
- Access organization admin functions
- View detailed requirement information (not stored online)


MID-LEVEL ACCESS (OFFICE SIGNATORIES)
--------------------------------------
WHO: Librarian, IT Director, Finance Officer, Registrar, etc.
     (One designated person per administrative office/department)

CAN:
- View all clearances for THEIR office/department only
- Approve students (change 'pending' → 'approved')
- Mark students as needing compliance (change to 'needs_compliance')
- View their office's clearance completion statistics
- Search/filter students by status
- Batch approve multiple students at once

CANNOT:
- Access other offices' clearances
- Modify office information or system settings
- Assign/remove signatory roles
- Override other offices' decisions
- Delete clearance records
- Modify academic terms
- View clearances from other departments


HIGH-LEVEL ACCESS (SYSTEM ADMINS / MIS)
----------------------------------------
WHO: MIS Staff, Super Admins

CAN:
- Add/remove/modify administrative offices in the system
- Assign/revoke office signatory roles
- Create/manage academic terms
- Generate system-wide reports across all offices
- View all clearances from all departments/offices
- Override any clearance decision (with full audit trail)
- Lock/unlock clearance records
- View complete audit logs
- Modify system settings
- Perform bulk operations
- Generate clearance reports for enrollment planning
- Temporarily disable offices (set requires_clearance = FALSE)

CANNOT:
- Nothing - full system access with complete accountability via audit logs


==============================================================
TECHNICAL NOTES & BEST PRACTICES
==============================================================

1. DATABASE INDEXES (For Performance)
   - CREATE INDEX idx_student_clearances_lookup ON STUDENT_CLEARANCES(student_id, term_id);
   - CREATE INDEX idx_clearance_items_lookup ON CLEARANCE_ITEMS(clearance_id, org_id);
   - CREATE INDEX idx_clearance_items_status ON CLEARANCE_ITEMS(status);
   - CREATE INDEX idx_org_admins_org ON ORGANIZATION_ADMINS(org_id);
   - CREATE INDEX idx_users_type ON USERS(user_type);

2. TRIGGERS TO IMPLEMENT
   - After INSERT/UPDATE on CLEARANCE_ITEMS → Update STUDENT_CLEARANCES.overall_status
   - After INSERT/UPDATE on CLEARANCE_ITEMS → Write to AUDIT_LOGS
   - After UPDATE on ORGANIZATION_ADMINS → Write to AUDIT_LOGS

3. STORED PROCEDURES
   - sp_GenerateClearancesForTerm(term_id) - Batch creates clearances for all students
   - sp_RecalculateOverallStatus(clearance_id) - Recalculates overall status
   - sp_GetStudentClearanceStatus(student_id, term_id) - Returns complete status

4. SECURITY
   - Hash all passwords with bcrypt or Argon2
   - Use prepared statements to prevent SQL injection
   - Implement session timeout (30 minutes inactivity)
   - Log all clearance status changes
   - Use HTTPS for all connections
   - Implement CSRF protection

5. NOTIFICATIONS (Recommended Features)
   - Email student when overall_status = 'approved'
   - Email student when any item becomes 'needs_compliance'
   - Email reminders 7 days before clearance_deadline
   - SMS option for urgent notifications

6. REPORTING (System Admin Functions)
   - Clearance completion rate per office/department
   - Students with incomplete clearances (before deadline)
   - Historical clearance trends per office
   - Bottleneck identification (which offices take longest to clear students)
   - Enrollment readiness report (how many students are fully cleared)
   - Per-office statistics: how many students approved vs pending vs needs compliance
   - Signatory activity logs (who approved what and when)

==============================================================
SAMPLE DATA SCENARIOS
==============================================================

SCENARIO 1: Student with NO pending requirements
-------------------------------------------------
Student: Juan Dela Cruz (2020-12345)
Term: First Semester 2024-2025

CLEARANCE_ITEMS generated:
1. Library → status: 'approved', is_auto_approved: TRUE
2. DIGITS → status: 'approved', is_auto_approved: TRUE
3. Finance → status: 'approved', is_auto_approved: TRUE
4. Registrar → status: 'approved', is_auto_approved: TRUE

STUDENT_CLEARANCES.overall_status = 'approved'
Result: Student can enroll immediately


SCENARIO 2: Student with library fines
---------------------------------------
Student: Maria Santos (2021-67890)
Term: First Semester 2024-2025

CLEARANCE_ITEMS generated:
1. Library → status: 'needs_compliance'
2. DIGITS → status: 'approved', is_auto_approved: TRUE
3. Finance → status: 'approved', is_auto_approved: TRUE
4. Registrar → status: 'approved', is_auto_approved: TRUE

STUDENT_CLEARANCES.overall_status = 'incomplete'

Action Required:
1. Maria sees "Needs Compliance" status for Library on her POES portal
2. Maria visits Library office physically to find out what she needs
3. Librarian informs her: "You have ₱150 unpaid fines"
4. Maria pays ₱150 at the library
5. Librarian (office signatory) clicks "APPROVE" button in the system
6. Library clearance → status: 'approved', approved_by: librarian_admin_id
7. System auto-updates overall_status = 'approved'
8. Maria can now enroll


SCENARIO 3: New signatory assignment
------------------------------------
Office: DIGITS (IT Department)
Previous Signatory: Mr. John Smith (retired)
New Signatory: Ms. Jane Doe

System Admin action:
1. Find Mr. John Smith in ORGANIZATION_ADMINS table
2. Set is_active: FALSE, removed_date: NOW
3. Create new ORGANIZATION_ADMINS record for Ms. Jane Doe
   - org_id: DIGITS office ID
   - position: "IT Director"
   - full_name: "Ms. Jane Doe"
4. Future approvals now show approved_by: jane_doe_admin_id

All historical clearances still show approved_by: john_smith_admin_id
(Maintains complete audit trail of who approved what and when)

NOTE: The DIGITS office itself (in ORGANIZATIONS table) never changes.
Only the signatory person (in ORGANIZATION_ADMINS) changes.


==============================================================
MIGRATION FROM CURRENT MANUAL SYSTEM
==============================================================

PHASE 1: Setup (Before semester starts)
1. Create all ORGANIZATIONS records (permanent administrative offices)
   - Academic Organization Treasurer
   - Academic Organization Adviser
   - Vice President for Student Development 
   - College Chief Librarian
   
2. Create all ORGANIZATION_ADMINS records (one signatory per office)
   - Assign the Librarian as Library signatory
   - Assign IT Director as DIGITS signatory
   - Assign Finance Officer as Finance signatory
   - etc.
   
3. Import student data into STUDENTS table

4. Create current ACADEMIC_TERM

NOTE: This is a ONE-TIME setup. Once configured, these offices remain 
permanently in the system and don't need to be re-added each semester.

PHASE 2: Pilot (Small batch)
1. Generate clearances for one department/year level
2. Train org admins on the system
3. Gather feedback and refine

PHASE 3: Full Rollout
1. Generate clearances for all enrolled students
2. Notify students via email about new system
3. Provide helpdesk support during transition
4. Keep manual backup process for one term

PHASE 4: Optimization
1. Analyze bottlenecks from first term data
2. Implement batch approval features if needed
3. Add conditional clearances (housing, etc.) if applicable
4. Integrate with other university systems

==============================================================
END OF DOCUMENTATION
==============================================================
