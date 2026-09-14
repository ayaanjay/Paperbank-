# PaperBank - Setup & Security Guide

**Last Updated:** March 20, 2026  
**Version:** 2.0 - Security & Management Enhanced

## ⚡ Quick Start

### Database Setup
1. Import the schema file into your MySQL database:
```bash
mysql -u root paperbank < schema.sql
```

Or manually run the schema.sql file in phpMyAdmin.

### Initial Admin Login
- **Email:** admin@paperbank.com
- **Password:** admin123
- ⚠️ **IMPORTANT:** Change this password immediately after first login!

### Folder Permissions
Ensure these folders are writable by the web server:
```bash
chmod 755 uploads/
chmod 755 uploads/papers/
chmod 755 config/
```

## 🔒 Security Features

### File Upload Limits
- **Maximum file size:** 50MB (increased from 10MB)
- **Allowed file types:** PDF, DOC, DOCX, TXT, JPG, JPEG, PNG
- **Link uploads:** Teachers can now upload direct links to resources

### PHP Configuration for Upload Limits
To enable the increased upload limits, update your `php.ini` file (usually located at `C:\xampp\php\php.ini`):

```ini
upload_max_filesize = 50M
post_max_size = 50M
max_execution_time = 300
max_input_time = 300
```

After updating, restart Apache in XAMPP control panel.

### Database Migration for Link Support
To enable link uploads, run the following SQL commands in phpMyAdmin or MySQL command line:

```sql
-- Add new columns to papers table for link support
ALTER TABLE `papers`
ADD COLUMN `upload_type` ENUM('file', 'link') NOT NULL DEFAULT 'file' AFTER `track`,
ADD COLUMN `link_url` VARCHAR(500) DEFAULT NULL AFTER `upload_type`;

-- Update existing records to have upload_type = 'file'
UPDATE `papers` SET `upload_type` = 'file' WHERE `upload_type` = '';

-- Add index for better performance
ALTER TABLE `papers` ADD KEY `idx_upload_type` (`upload_type`);
```

Or use the provided migration file: `migration_add_link_support.sql`

1. **Automatic Migration:** Visit `http://localhost/PaperBank/migrate_password_security.php` in your browser
2. **Manual Migration:** Or run this SQL in phpMyAdmin:
```sql
ALTER TABLE `users` ADD COLUMN `password_changed` tinyint(1) NOT NULL DEFAULT 0 AFTER `status`;
UPDATE `users` SET `password_changed` = 0 WHERE `role` = 'teacher' AND `status` = 'approved';
```

#### Preserve Papers on Teacher Deletion
If you want papers to remain available even when teachers are deleted, run this migration:

1. **Automatic Migration:** Visit `http://localhost/PaperBank/migrate_preserve_papers.php` in your browser
2. **What it does:** Changes the foreign key constraint so papers are preserved when teachers are deleted
3. **Display:** Deleted teachers' papers will show "N/A" as the teacher name

### Session Security
- Session fixation prevention via `session_regenerate_id()`
- Session tokens validated on every sensitive operation

### Upload Folder Protection
- `.htaccess` file prevents direct file access
- Script execution disabled in uploads folder
- Directory listing disabled for privacy

### Automatic Paper Approval
- Papers uploaded by teachers are automatically approved and published
- No admin review required - papers are immediately available to students
- Teachers can upload papers directly to the system without waiting for approval

## 📋 New Features

### User Account Management (`teacher/account.php`)
- Teachers can update their profile (name, email)
- Teachers can change their own password
- Requires current password verification for password changes

### Admin User Management (`admin/manage_users.php` & `admin/edit_user.php`)
- View all teachers with statistics
- Edit teacher profiles and roles
- Reset teacher passwords
- Approve or delete pending teacher accounts
- Real-time statistics (total, approved, pending)

### Enhanced Admin Dashboard (`admin/dashboard.php`)
**New Metrics:**
- Total papers published
- Storage usage (MB)
- Teacher count and status
- Papers by category distribution (visual bar chart)
- Papers by grade distribution (visual bar chart)
- Top teachers by paper count
- Recent uploads and teachers

## 🗄️ Database Schema

### Tables

#### `users`
```sql
- id (INT, Primary Key)
- full_name (VARCHAR)
- email (VARCHAR, UNIQUE)
- password (VARCHAR, hashed)
- role (ENUM: student, teacher, admin)
- status (ENUM: pending, approved, rejected)
- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)
```

#### `papers`
```sql
- id (INT, Primary Key)
- title (VARCHAR)
- grade (INT)
- subject (VARCHAR)
- category (VARCHAR)
- year (INT)
- track (VARCHAR: UK or US for grades 9-12)
- file_path (VARCHAR)
- file_size (INT, in bytes)
- teacher_id (INT, Foreign Key → users, NULLABLE - papers preserved when teacher deleted)
- status (ENUM: pending, approved, rejected)
- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)
```

#### `activity_log` (Optional)
Tracks all user actions for security and audit purposes.

#### `downloads_log` (Optional)
Tracks all file downloads for analytics.

## 🔧 Account Management

### For Teachers
1. Click "Account" button in navbar
2. **Update Profile Section:**
   - Change full name
   - Change email address
3. **Change Password Section:**
   - Enter current password (verified)
   - Enter new password (min 6 characters)
   - Confirm new password
   - Submit to change

### For Admins
1. Go to "Manage Teachers" from admin dashboard
2. Click "Edit" button next to a teacher
3. **Modify User Profile:**
   - Change name
   - Change email
   - Change role (student/teacher/admin)
   - Change status (pending/approved/rejected)
4. **Reset Password:**
   - Use "Reset Password" section at bottom
   - Set new password (min 6 characters)
   - User will need to use this new password to login

## 📊 Admin Dashboard Metrics

### Statistics Displayed
- **Total Papers:** Count of all papers in system
- **Published:** Count of all papers (auto-approved upon upload)
- **Teachers:** Count of registered teachers
- **Storage (MB):** Total file storage used by all papers

### Distribution Charts
- **Papers by Category:** Bar chart showing breakdown by Test/Homework/Classwork/Exam
- **Papers by Grade:** Bar chart showing distribution across grades

### Activity Tables
- **Top Teachers:** Teachers ranked by number of papers uploaded
- **Recent Uploads:** Latest 5 papers uploaded with timestamps
- **Recent Teachers:** Latest 5 teachers who registered

## 🚀 File Download System

### How It Works
1. User clicks download link on any paper
2. System checks `download.php` for access control
3. Validates user permissions:
   - Teachers → can only download their own papers
   - Students → can download all published papers (auto-approved upon upload)
   - Admins → can download any published paper
4. File served with proper HTTP headers (prevents direct execution)
5. Browser prompts user to save file

### Download Link Format
```html
<a href="/PaperBank/download.php?id=PAPER_ID">Download Paper</a>
```

## ⚠️ Remaining Security Improvements

### Phase 1 (Recommended Before Production)
- ✅ CSRF token protection on all forms
- ✅ File download wrapper with access control
- ✅ Password change functionality
- ✅ User management by admin
- ⏳ **Remaining:** Prepared statements for all filter queries, Session cookie hardening

### Phase 2 (First Month)
- Pagination for large datasets (500+ papers)
- Password reset email functionality
- Audit logging for all admin actions
- Rate limiting on login attempts

### Phase 3 (Future Enhancements)
- Two-factor authentication (2FA)
- Email verification on registration
- File virus scanning on upload
- Backup and disaster recovery system

## 🔑 API Endpoints

### Public Endpoints
- `GET /index.php` - Home page
- `GET /browse/subjects.php` - Browse by subjects (grade selection)
- `GET /browse/all_papers.php` - View all published papers with filters (auto-approved upon upload)
- `GET /download.php?id=<PAPER_ID>` - Download paper (with auth check)

### Teacher Endpoints
- `GET /teacher/dashboard.php` - Teacher home
- `GET /teacher/account.php` - Manage account
- `POST /teacher/upload.php` - Upload new paper
- `GET /teacher/my_papers.php` - View own papers
- `POST /teacher/edit.php?id=<PAPER_ID>` - Edit paper metadata
- `GET /teacher/delete.php?id=<PAPER_ID>` - Delete own paper

### Admin Endpoints
- `GET /admin/dashboard.php` - Admin home with metrics
- `GET /admin/manage_users.php` - Manage teachers
- `GET /admin/edit_user.php?id=<USER_ID>` - Edit user
- `GET /admin/papers.php` - View all papers with filters
- `GET /admin/subjects.php` - Manage subjects

## 🛠️ Configuration

### Main Config File: `config/db.php`
```php
$conn = new mysqli("localhost", "root", "", "paperbank");
```
Update database credentials if needed.

### Subject Configuration: `config/subjects.php`
Defines available subjects for each grade. Edit this to add/remove subjects.

## 📝 CSRF Token Usage

### In HTML Forms
```html
<form method="POST">
    <?php echo csrfField(); ?>
    <!-- rest of form -->
</form>
```

### In PHP Processing
```php
<?php
include("../includes/csrf.php");

if (!validateCSRFToken()) {
    $_SESSION['message'] = ['text' => 'Security validation failed.', 'type' => 'error'];
    exit();
}
// Process form
?>
```

## 🐛 Common Issues & Solutions

### Issue: "File too large" error on upload
**Solution:** Increase PHP upload limits in `php.ini`:
```ini
upload_max_filesize = 20M
post_max_size = 25M
```

### Issue: "Permission denied" writing to uploads folder
**Solution:** Fix folder permissions:
```bash
chmod 777 uploads/
chmod 777 uploads/papers/
```

### Issue: Admin password not working
**Solution:** Use default credentials in schema.sql or reset via database:
```sql
UPDATE users SET password='$2y$10$6gg.QGHfW5oCnW5cN2yUWujJb..DXLpBavG0LCMA3lJhJu0YsqCB.' 
WHERE role='admin' LIMIT 1;
-- Password is: admin123
```

### Issue: Download.php shows "File not found"
**Solution:** Ensure papers table has correct `file_path` values. File path should be relative path (e.g., "1772356099_69a40203ef10f.txt")

## 📞 Support & Maintenance

### Regular Maintenance Tasks
1. **Weekly:** Review activity logs for suspicious behavior
2. **Monthly:** Check storage usage and clean old/duplicate papers
3. **Monthly:** Backup database and upload folder
4. **Quarterly:** Review and update security measures

### Backup Command
```bash
mysqldump -u root paperbank > paperbank_backup.sql
tar -czf paperbank_uploads.tar.gz uploads/
```

## ✅ Implementation Checklist

- ✅ CSRF token system implemented and integrated
- ✅ Secure file download wrapper created
- ✅ User account management page added
- ✅ Admin user editing page created
- ✅ Admin dashboard metrics enhanced
- ✅ Password change functionality added
- ✅ .htaccess security file created
- ✅ Database schema file created
- ✅ CSRF tokens added to all critical forms
- ✅ SQL injection vulnerabilities fixed in key areas
- ⏳ Session cookie hardening (next phase)
- ⏳ Pagination system (next phase)

---

**For questions or issues, refer to the configuration sections above or check server logs for error details.**
