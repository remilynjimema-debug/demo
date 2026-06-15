# ESTI College Grading Management System
## Setup Instructions

### Project Structure
```
esti/
├── index.html          ← Main frontend (all pages)
├── api.php             ← PHP backend API (all CRUD)
├── database.sql        ← MySQL schema + seed data
├── includes/
│   └── db.php          ← DB connection & helpers
└── README.md
```

---

### 1. Requirements
- PHP 7.4+ (with MySQLi extension)
- MySQL 5.7+ or MariaDB 10+
- A web server: Apache (XAMPP/LAMP) or Nginx

---

### 2. Database Setup
1. Open **phpMyAdmin** (or MySQL CLI)
2. Create a new database: `esti_grading_db`
3. Import `database.sql`
   - In phpMyAdmin: Import → Choose `database.sql` → Go
   - Or via CLI: `mysql -u root -p < database.sql`

**Default admin account:**
- Username: `admin`
- Password: `admin123`

---

### 3. Configure Database Connection
Edit `includes/db.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');      // your MySQL username
define('DB_PASS', '');          // your MySQL password
define('DB_NAME', 'esti_grading_db');
```

---

### 4. Deploy to Web Server

**Using XAMPP (Windows/Mac):**
1. Copy the `esti/` folder to `C:/xampp/htdocs/`
2. Start Apache and MySQL in XAMPP Control Panel
3. Visit: `http://localhost/esti/index.html`

**Using LAMP (Linux):**
1. Copy to `/var/www/html/esti/`
2. Visit: `http://localhost/esti/index.html`

---

### 5. Switch from Demo Mode to Live Mode

In `index.html`, find this line near the top of the `<script>` section:
```javascript
const DEMO = true; // Set to false when PHP backend is ready
```

Change it to:
```javascript
const DEMO = false;
```

This makes all CRUD operations go through `api.php` → MySQL.

---

### Features
| Module           | List | Add | Edit | Delete | Search | Filter | Pagination |
|-----------------|------|-----|------|--------|--------|--------|------------|
| Dashboard       | ✅   | —   | —    | —      | —      | ✅     | —          |
| Students        | ✅   | ✅  | ✅   | ✅     | ✅     | ✅     | ✅         |
| Subjects        | ✅   | ✅  | ✅   | ✅     | ✅     | —      | —          |
| Class & Section | ✅   | ✅  | ✅   | ✅     | ✅     | —      | —          |
| Departments     | ✅   | ✅  | ✅   | ✅     | —      | —      | —          |
| Faculty         | ✅   | ✅  | ✅   | ✅     | ✅     | —      | —          |

### Pages
1. **Login** – Admin authentication
2. **Dashboard** – Stats, enrollment chart, grade donut, recent students
3. **Student Management** – Full CRUD with search, course/year filters, pagination
4. **Subject Management** – Full CRUD with search
5. **Class & Section** – Full CRUD with search
6. **Department Management** – Full CRUD
7. **Faculty Management** – Full CRUD with search

### Tech Stack
- **Frontend:** HTML5, CSS3, Vanilla JavaScript, Chart.js
- **Backend:** PHP 7.4+, MySQLi
- **Database:** MySQL / MariaDB
- **Icons:** Font Awesome 6
- **Fonts:** Inter + Plus Jakarta Sans (Google Fonts)
