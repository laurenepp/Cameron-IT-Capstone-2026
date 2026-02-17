# Security Folder

This folder contains the core security framework for the clinic management system.

## Files

| File | What It Does |
|------|-------------|
| `config.sample.php` | **Template** for database connection. Copy → rename to `config.php` → fill in DB details. |
| `config.php` | Your actual config with DB password. **NOT in GitHub** (blocked by .gitignore). |
| `auth.php` | Login, logout, session management, audit logging. |
| `rbac.php` | Role permissions. Controls who can view/add/edit/delete each resource. |
| `validation.php` | Input sanitization and form validation. Prevents SQL injection and XSS. |

## Setup Instructions (For New Team Members)

1. Clone the repo
2. Copy `security/config.sample.php` → `security/config.php`
3. Open `config.php` and fill in your database name
4. Import the database SQL from the `database/` folder into phpMyAdmin
5. Start UniServer and go to `http://localhost/`

## How To Secure a New Page

Add these lines at the very top of any PHP page that needs to be protected:

```php
<?php
require_once '../security/config.php';
require_once '../security/auth.php';
require_once '../security/rbac.php';
require_once '../security/validation.php';

requireLogin();                          // Must be logged in
requireRole(['Administrator', 'Nurse']); // Must have one of these roles
?>
```

## Roles & Permissions Summary

| Role | Patient | Schedule | Visit Notes | Users | Insurance | Emergency Contact |
|------|---------|----------|-------------|-------|-----------|------------------|
| Administrator | Full | Full | Full | Full | Full | Full |
| Doctor | View | View | View, Add | — | — | — |
| Nurse | View | View | View, Add | — | — | — |
| Office Manager | View | View, Add, Edit | — | Full | Full | Full |
| Receptionist | View, Add, Edit | View, Add, Edit | — | — | Full | Full |

**Notes:**
- Only Administrator can delete anything
- Doctor and Nurse medical access is limited to assigned patients only
