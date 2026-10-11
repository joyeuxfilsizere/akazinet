# AkaziNet - Professional Job Vacancy Platform for Rwanda

**Connecting Rwandans to Career Opportunities**

A modern, secure, and production-ready job vacancy platform built for the Rwandan job market.

## Tech Stack

- HTML5, CSS3, Tailwind CSS (CDN)
- Vanilla JavaScript (ES6+)
- PHP 8+
- MySQL with PDO (Prepared Statements)
- Apache (.htaccess)

## Features

### Public (Visitors)
- Browse all verified job vacancies
- Search by title, company, or keyword
- Filter by category, province, district, employment type
- View detailed job descriptions, requirements, and benefits
- WhatsApp contact for each vacancy
- Social media sharing
- Browse companies and categories
- Contact form
- Fully responsive design
- No account required

### Admin Panel
- Secure login with password hashing
- Dashboard with statistics
- CRUD operations for jobs, companies, categories
- Image upload for company logos
- Site settings management
- CSRF protection

## Installation

### Prerequisites
- PHP 8.0+
- MySQL 5.7+ or MariaDB 10.3+
- Apache with mod_rewrite enabled
- XAMPP, WAMP, MAMP, or similar stack

### Steps

1. **Clone/Download** the project files to your web server directory:
   ```
   Copy the `kazinet` folder to your htdocs/www directory
   ```

2. **Create the database:**
   - Open phpMyAdmin or MySQL command line
   - Import `database/schema.sql` to create tables
   - Import `database/seed.sql` to add sample data

3. **Configure database connection:**
   Edit `config/database.php` and update:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'kazinet');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   ```

4. **Configure Site URL:**
   ```php
   define('SITE_URL', 'http://localhost/kazinet');
   ```

5. **Set folder permissions:**
   Ensure `assets/uploads/` is writable by the web server:
   ```bash
   chmod -R 755 assets/uploads/
   ```

6. **Enable Apache mod_rewrite** (if not already):
   ```bash
   sudo a2enmod rewrite
   sudo systemctl restart apache2
   ```

7. **Access the site:**
   - Website: `http://localhost/kazinet/`
   - Admin: `http://localhost/kazinet/login.php`

### Default Admin Credentials
- **Username:** admin
- **Email:** contact@akazinet.com
- **Password:** admin123

> **Important:** Change the admin password immediately after first login.

## Folder Structure

```
kazinet/
├── admin/              # Admin panel pages
│   └── includes/       # Admin header & footer
├── assets/
│   ├── css/            # Stylesheets
│   ├── js/             # JavaScript files
│   ├── images/         # Static images
│   └── uploads/        # Uploaded files (logos)
│       └── logos/       # Company logos
├── config/             # Database configuration
├── database/           # SQL schema & seed data
├── includes/           # Shared PHP components
├── components/         # Reusable UI components
├── pages/              # Public pages (about, contact, etc.)
├── api/                # API endpoints
├── index.php           # Homepage
├── jobs.php            # Job listings
├── job-details.php     # Single job view
├── companies.php       # Companies listing
├── company-details.php # Single company view
├── login.php           # Admin login
├── .htaccess           # Apache configuration
└── README.md
```

## Database Schema

- **admins** - Administrator accounts
- **companies** - Company profiles
- **categories** - Job categories
- **jobs** - Job vacancies with full details
- **settings** - Platform configuration
- **contact_messages** - Contact form submissions

## Security Features

- PDO prepared statements (SQL injection prevention)
- XSS prevention via htmlspecialchars
- Password hashing with password_hash()
- CSRF token protection
- Session-based admin authentication
- File upload validation (type, size)
- Admin pages protected from unauthorized access
- Sensitive files blocked via .htaccess

## License

MIT License - Free to use and modify.
