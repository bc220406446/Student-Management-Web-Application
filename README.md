# Student Management Web Application

## Introduction

In many schools and small organizations, student records are still managed using manual systems such as paper registers or simple files. These systems can cause problems such as data loss, difficulty in searching records, time-consuming updates, and human error. Managing a large amount of student information manually becomes inefficient and unreliable over time.

The **Student Management Web Application** is a simple web-based system designed to store and manage student information in an organized and secure way. It allows students and administrators to perform different tasks through an easy-to-use web interface. Student information is stored in a structured MySQL database, which reduces manual record keeping and makes records easier to manage.

This project demonstrates basic web development concepts such as forms, database connections, user authentication, role-based access, and CRUD (Create, Read, Update, and Delete) operations. It is suitable for beginner-level students, small organizations, and academic assignments.

## Functional Requirements

1. A student can register in the system.
2. Student registration must be approved by the administrator before login.
3. A student can log in after administrator approval.
4. A student can view only their own record.
5. A student can search for their own record by student ID.
6. A student can update only their own profile information.
7. A student can log out from the system.
8. An administrator can log in to the system.
9. An administrator can approve or reject student registrations.
10. An administrator can add a new student record.
11. An administrator can view all student records.
12. An administrator can update any student's information.
13. An administrator can delete a student record.
14. The system displays success and error messages for user actions.

## Tools and Technologies

### Front End

- HTML
- CSS
- JavaScript

### Back End

- PHP

### Database

- MySQL

### Server

- XAMPP
- Apache Server

## System Setup

### Requirements

Before setting up the project, install XAMPP on the computer. XAMPP provides the Apache web server, PHP, MySQL, and phpMyAdmin required to run the application.

### Step 1: Copy the Project Folder

1. Locate the XAMPP installation directory.
2. Open the `htdocs` folder. Its usual location is:

   ```text
   C:\xampp\htdocs
   ```

3. Copy the complete `SMWA` project folder into `htdocs`.
4. The final project location should be:

   ```text
   C:\xampp\htdocs\SMWA
   ```

### Step 2: Start Apache and MySQL

1. Open the **XAMPP Control Panel**.
2. Click **Start** next to **Apache**.
3. Click **Start** next to **MySQL**.

Both services should be running before opening the application.

### Step 3: Import the Database Backup

1. Open a web browser.
2. Go to [http://localhost/phpmyadmin](http://localhost/phpmyadmin).
3. Click the **Import** tab.
4. Click **Choose File**.
5. Select the `smwa.sql` database backup from the `SMWA` project folder.
6. Click **Import** or **Go** at the bottom of the page.

The SQL backup automatically creates the `SMWA` database, the administrator table, the student table, and the default administrator account.

### Step 4: Run the Application

Open the following address in a web browser:

[http://localhost/SMWA/](http://localhost/SMWA/)

The application will open the login page.

## Default Administrator Login

- **Email:** `admin@smwa.com`
- **Password:** `Test@123`

The administrator can use this account to approve student registrations and manage student records.

## Demo Student Login

Use the following approved student account to test the student portal:

- **Email:** `jahangeer@gmail.com`
- **Password:** `Test@123`

This demo account is created automatically when `smwa.sql` is imported.

## Basic System Workflow

1. A student opens the registration page and submits their information.
2. The new account remains pending until the administrator reviews it.
3. The administrator logs in and approves or rejects the registration.
4. An approved student can log in and access the student dashboard.
5. The student can view, search, and update only their own information.
6. The administrator can add, view, update, or delete student records.
