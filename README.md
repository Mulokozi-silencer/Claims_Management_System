# Claims_Management_System
A web-based Claims Management System designed to streamline the process of submitting,reviewing,&amp; managing claims efficiently.The system supports multiple user roles (Claimant, Adjuster, Admin) and provides real-time tracking of claim status from submission to settlement.  This project is built using PHP, MySQ, and HTML/CSS, and is easy to deploy.

# ClaimsPro — Claims Management System

## Installation Guide

### Requirements
- PHP 8.0+
- MySQL 5.7+ or MariaDB 10.3+
- Apache or Nginx with mod_rewrite
- Web browser (Chrome, Firefox, Edge, Safari)

---

### Step 1 — Set Up Database

1. Open **phpMyAdmin** (or MySQL CLI)
2. Run the `database.sql` file to create the database and tables with demo data:
   ```
   mysql -u root -p < database.sql
   ```
   Or paste contents into phpMyAdmin > SQL tab.

---

### Step 2 — Configure Database Connection

Edit `includes/config.php`:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');        // your MySQL username
define('DB_PASS', '');            // your MySQL password
define('DB_NAME', 'claims_db');
define('APP_URL', 'http://localhost/claims-system');
```

> ⚠️ Change `APP_URL` to match where you place the folder.

---

### Step 3 — Deploy Files

Copy the entire `claims-system/` folder to your web server root:
- **XAMPP**: `C:/xampp/htdocs/claims-system/`
- **WAMP**: `C:/wamp64/www/claims-system/`
- **Linux**: `/var/www/html/claims-system/`

Ensure the `uploads/` directory is writable:
```bash
chmod 755 uploads/
```

---

### Step 4 — Access the System

Open: `http://localhost/claims-system/`

---

## Default Login Credentials

| Role      | Email                        | Password  |
|-----------|------------------------------|-----------|
| Admin     | mulokoziwillium@gmail.com    | Mulokozi  |
| Adjuster  | sarah.mitchell@claimsys.com  | password  |
| Adjuster  | james.carter@claimsys.com    | password  |
| Claimant  | winfrida@gmail.com            | Winfrida  |
| Claimant  | robert@example.com           | password  |

> **Change all passwords immediately in production!**

---

## System Features

### Claimant Features
- Register / Login
- File new insurance claims (auto, health, property, life, travel, liability)
- Save claims as drafts before submission
- Upload supporting documents (PDF, JPG, PNG, DOC, DOCX, XLS)
- Track claim status in real time
- Receive notifications on status changes
- Add comments to claims
- Edit profile and change password

### Adjuster Features
- View all submitted/assigned claims
- Assign claims to adjusters
- Update claim status (Review → Approve/Reject/Settle)
- Set approved payout amounts
- Add rejection reasons
- Upload documents
- Activity timeline management

### Admin Features
- All adjuster capabilities
- User management (create, toggle status, change roles)
- Reports & Analytics dashboard
  - Claims by status, type, priority
  - Monthly trends
  - Financial summaries
  - Top claimants

---

## File Structure

```
claims-system/
├── index.php              # Login page
├── dashboard.php          # Main dashboard
├── claims.php             # Claims list with filters
├── new-claim.php          # New / Edit claim form
├── claim-detail.php       # Claim detail & actions
├── users.php              # User management (admin)
├── reports.php            # Analytics & reports (admin)
├── notifications.php      # Notifications center
├── profile.php            # User profile
├── logout.php             # Logout handler
├── database.sql           # Database schema + seed data
├── css/
│   └── style.css          # Main stylesheet (dark luxury theme)
├── includes/
│   ├── config.php         # DB config, helpers, session
│   ├── layout.php         # Shared header + sidebar
│   └── layout-end.php     # Footer + global JS
├── php/
│   └── mark-read.php      # Mark notifications read
└── uploads/               # Document uploads (writable)
```

---

## Security Notes for Production

1. Set `DB_PASS` to a strong password
2. Use HTTPS and set `'secure' => true` in session cookie params
3. Change all demo passwords immediately
4. Set `APP_URL` to your actual domain
5. Review PHP `display_errors` (disable in production)
6. Add `.htaccess` restrictions on `uploads/` and `includes/`

---

## Built With

- **PHP 8** — Backend logic and API
- **MySQL** — Relational database
- **HTML5 / CSS3** — Frontend markup and styling
- **Vanilla JavaScript** — UI interactions
- **Google Fonts** — Playfair Display + DM Sans + JetBrains Mono

*ClaimsPro v2.0 — Professional Claims Management System*
