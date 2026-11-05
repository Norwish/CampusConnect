# CampusConnect — Campus Event Registration System

A responsive PHP/MySQL campus event registration starter project.

## Features
- Public upcoming-events listing.
- Student registration form.
- Server-side capacity enforcement using a database transaction.
- Duplicate email/student-ID prevention per event.
- Admin login and dashboard.
- Create and cancel events.
- Attendee list and check-in toggle.
- Responsive layout for phones and desktops.

## Files
- `index.php` — public event listing
- `register.php` — participant registration
- `admin-login.php`, `admin.php`, `logout.php` — administration
- `install.php` — one-time database table and admin setup
- `config.php` — database connection and shared helpers
- `assets/style.css`, `assets/app.js` — frontend assets
- `database.sql` — alternative manual database schema

## Hosting setup
This project needs PHP 8.0+ with PDO MySQL and a MySQL/MariaDB database. GitHub Pages alone cannot run PHP.

1. Create a MySQL database and database user in your PHP hosting control panel.
2. Edit `config.php` and replace `DB_NAME`, `DB_USER`, and `DB_PASS` with the supplied credentials. Keep these credentials private.
3. Upload the project files to the hosting account's web root (often `public_html`).
4. Visit `https://your-domain.example/install.php`.
5. Create your administrator username and password. Use a strong password.
6. **Immediately delete `install.php` from the server** after successful setup.
7. Open `index.php` to view the public site and `admin-login.php` to manage events.

You can also create the tables by importing `database.sql` in phpMyAdmin, then create an admin record separately. The `install.php` route is simpler for a first setup.

## Important security notes
- Never commit real database credentials to a public GitHub repository.
- Use HTTPS on the live site.
- Delete `install.php` after setup.
- Back up the database.
- This is a student project starter, not a fully audited production system. Add email verification, password reset, rate limiting, privacy notice, and stronger admin account management before real institutional use.

## Phone workflow
You can edit the files with an Android code editor and upload them to GitHub. To run PHP/MySQL, deploy to a PHP-capable host; GitHub Pages only serves static HTML/CSS/JS.
