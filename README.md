# Troy L. Selibio – Secure Dynamic Portfolio (PHP + MySQL)

Final Project – ITS 300 Section B. This is the midterm static portfolio upgraded into a database-driven website with a secure administrator dashboard.

## Features

**Public website** (all content comes from the database)
- Home, About, Skills, Projects (with category filter), Resume, Contact
- Dark / light mode, responsive layout (desktop, tablet, phone)
- Old `.html` addresses redirect to the new `.php` pages

**Admin dashboard** (`/admin/login.php`)
- Statistics: projects, skills, experience, education, achievements, messages, unread
- CRUD: Skills, Projects (with image upload), Education, Experience, Achievements
- Personal information (name, bio, contact details, profile picture, social links)
- Website settings (title, logo, footer, default theme, accent colour)
- Contact messages: view, mark read/unread, delete
- Account page: change username / password

**Security**
- Passwords hashed with `password_hash()` / `password_verify()`; session ID regenerated on login
- Login throttling: 5 failed attempts per IP in 15 minutes, then a temporary lockout
- 30-minute idle session timeout; cookies are `HttpOnly`, `SameSite=Lax`, `Secure` on HTTPS
- PDO prepared statements everywhere (SQL injection)
- Output escaped with `htmlspecialchars` (XSS) + Content-Security-Policy and other security headers
- CSRF token on every form that changes data (including logout and delete buttons)
- Server-side validation on every field; URLs limited to `http`/`https`
- Contact form: CSRF, honeypot field, minimum fill time, per-IP rate limit (5 per hour)
- Image uploads: real content check (`finfo` + `getimagesize`), JPG/PNG/WEBP/GIF only, 2 MB limit, random server-generated file names, PHP execution disabled in `uploads/`
- Database credentials live in `includes/config.php` (git-ignored); `includes/` and `database/` are blocked from the web
- Errors are logged on the server; visitors only see generic messages

## Requirements
- PHP 8.0+ with `pdo_mysql`, `fileinfo`, `mbstring`
- MySQL 5.7+ / MariaDB 10.3+
- Apache with `mod_rewrite` and `AllowOverride All` (XAMPP works out of the box)

## Installation (local with XAMPP)
1. Copy this folder to `C:\xampp\htdocs\portfolio`.
2. Start **Apache** and **MySQL** in the XAMPP control panel.
3. Open phpMyAdmin → **Import** → choose `database/portfolio.sql` (creates the `portfolio_db` database with sample data).
4. Copy `includes/config.sample.php` to `includes/config.php` and edit the database values if needed.
5. Open `http://localhost/portfolio/`.

## Administrator setup
No administrator password is stored in the SQL file or in GitHub. Create the account once:
1. In `includes/config.php` set `ADMIN_SETUP_KEY` to a long random value.
2. Open `/admin/setup.php`, enter the setup key, a username and a password (min. 10 characters).
3. **Delete `admin/setup.php`** and set `ADMIN_SETUP_KEY` back to `''`.
4. Log in at `/admin/login.php`.

## Deploying to the live (midterm) hosting
1. Upload all files to the same folder/URL used for the midterm.
2. Create the MySQL database in your hosting panel and import `database/portfolio.sql` (remove the `CREATE DATABASE` / `USE` lines if your host does not allow them).
3. Create `includes/config.php` on the server with the hosting database credentials.
4. Follow *Administrator setup* above.
5. Turn on HTTPS and uncomment the HTTPS redirect at the bottom of `.htaccess`.
6. Make sure the `uploads/` folder is writable by PHP (usually `755` or `775`).
7. Upload your resume PDF (`TROY-RESUME-Final.pdf`) to the site folder.

## Folder structure
```
portfolio/
├── index.php  about.php  skills.php  projects.php  resume.php  contact.php
├── admin/            login, logout, setup, dashboard, profile, skills, projects,
│                     education, experience, achievements, messages, settings, account
├── includes/         bootstrap, config, db, security, auth, functions, admin_ui, header, footer
├── assets/           css/ js/ images/
├── uploads/          admin-uploaded images (script execution disabled)
├── database/         portfolio.sql
└── README.md
```

## Screenshots
Add your screenshots to a `screenshots/` folder: public pages, admin login, dashboard, CRUD operations, phpMyAdmin tables, and the mobile layout.
