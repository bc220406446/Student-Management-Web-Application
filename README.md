# Student Management Web Application

A PHP + MySQL web application for managing student accounts and records with role-based access for **Admin** and **Student** users.

## Features

- Role-based login (Admin / Student)
- Student self-registration with admin approval flow
- Admin dashboard with student statistics
- Admin student management:
  - View all students
  - Add student accounts
  - Edit student records
  - Review and approve/reject pending registrations
- Student dashboard:
  - View own record
  - Search student record by ID
  - Update personal profile
- Password reset flow for students

## Tech Stack

- PHP (PDO)
- MySQL / MariaDB
- HTML, CSS, JavaScript

## Project Structure

```text
.
├── admin/          # Admin pages
├── student/        # Student pages
├── includes/       # Shared auth, DB config, headers, modals
├── assets/         # Styles and scripts
├── login.php
├── register.php
├── forgot-password.php
└── setup.sql       # Database schema + default admin seed
```

## Prerequisites

- PHP 7.4+ (or newer)
- MySQL / MariaDB
- Web server (Apache/Nginx) or local stack (XAMPP/WAMP/LAMP)

## Setup

1. Clone the repository.
2. Create the database and tables:
   - Import `/setup.sql` into MySQL.
3. Configure database credentials in:
   - `/includes/db.php`
   - Default values are:
     - host: `localhost`
     - database: `smwa`
     - user: `root`
     - password: *(empty)*
4. Serve the project from your web server document root.
5. Open `login.php` in your browser.

## Default Admin Login

- **Email:** `admin@example.com`
- **Password:** `Test@123`

> Change the default admin password after first login.

## Main User Flows

- **Student registration:** `register.php` → status set to `Pending` → admin approves/rejects.
- **Admin portal:** manage student records and registration approvals from `/admin`.
- **Student portal:** access personal record and profile actions from `/student`.

## Notes

- Passwords are stored using PHP `password_hash`.
- Input validation and prepared statements are used for core auth/data operations.
