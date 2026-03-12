# Student Feedback App

A simple PHP + MySQL web application that collects and displays student course feedback.  
Served by Apache2 on a custom port (8082) with DNS pointing to `feedback.local`.

---

## Project Structure

```
class-experiment/
├── public/                  # Web root (Apache DocumentRoot)
│   ├── index.php            # Main page: form + feedback table
│   ├── db.php               # Database connection helper
│   └── style.css            # Styles
├── database/
│   └── setup.sql            # CREATE DATABASE / TABLE / user + sample data
├── apache/
│   ├── student-feedback.conf  # Apache2 virtual host (port 8082)
│   └── ports.conf             # Updated ports.conf with Listen 8082
└── README.md
```

---

## Part 1 – Application Overview

| Feature | Detail |
|---|---|
| Language | PHP 8.x |
| Database | MySQL 8.x (via `mysqli`) |
| Web Server | Apache2 |
| Form fields | Name, Email, Star Rating (1–5), Comment |
| DB write | `INSERT INTO feedback …` on form submit |
| DB read | `SELECT * FROM feedback ORDER BY submitted_at DESC` |

---

## Part 2 – Database Setup

### Prerequisites
- MySQL 8.x running locally
- A user with `CREATE DATABASE` / `CREATE USER` privileges (e.g. `root`)

### Run the setup script

```bash
mysql -u root -p < database/setup.sql
```

This will:
1. Create the `student_feedback` database
2. Create the `feedback` table
3. Create a least-privilege app user `appuser` / `AppPass123!`
4. Insert 5 sample rows

### Manual commands (same as setup.sql)

```sql
CREATE DATABASE IF NOT EXISTS student_feedback
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE student_feedback;

CREATE TABLE IF NOT EXISTS feedback (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name         VARCHAR(120) NOT NULL,
    email        VARCHAR(255) NOT NULL,
    rating       TINYINT      NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment      TEXT,
    submitted_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE USER IF NOT EXISTS 'appuser'@'localhost' IDENTIFIED BY 'AppPass123!';
GRANT SELECT, INSERT, UPDATE, DELETE ON student_feedback.* TO 'appuser'@'localhost';
FLUSH PRIVILEGES;
```

> **Security note:** Change `AppPass123!` before deploying to any public-facing server,  
> and update the same value in `public/db.php`.

---

## Part 3 – Apache2 Virtual Host

### 1. Copy the virtual host file

```bash
sudo cp apache/student-feedback.conf /etc/apache2/sites-available/
```

### 2. Enable the site and required modules

```bash
sudo a2ensite student-feedback.conf
sudo a2enmod rewrite
```

### 3. Add port 8082 to ports.conf

```bash
echo "Listen 8082" | sudo tee -a /etc/apache2/ports.conf
```

Or replace `/etc/apache2/ports.conf` with the provided `apache/ports.conf`.

### 4. Copy the application files

```bash
sudo mkdir -p /var/www/student-feedback
sudo cp -r public/ /var/www/student-feedback/public
sudo chown -R www-data:www-data /var/www/student-feedback
```

### 5. Reload Apache

```bash
sudo apache2ctl configtest   # verify syntax
sudo systemctl reload apache2
```

### Virtual host summary

| Setting | Value |
|---|---|
| Port | **8082** |
| ServerName | `feedback.local` |
| DocumentRoot | `/var/www/student-feedback/public` |
| Error log | `/var/log/apache2/student-feedback-error.log` |
| Access log | `/var/log/apache2/student-feedback-access.log` |

---

## Part 4 – DNS Configuration

### Option A – Local testing with `/etc/hosts`

Add this line to `/etc/hosts` on any client machine (or the server itself):

```
<YOUR_SERVER_IP>   feedback.local
```

```bash
# Example (replace with real IP)
echo "192.168.1.50   feedback.local" | sudo tee -a /etc/hosts
```

Then verify:

```bash
ping -c 3 feedback.local
curl -s http://feedback.local:8082/ | head -20
```

### Option B – Public DNS (e.g. Cloudflare, Route 53)

| Type | Name | Value | TTL |
|---|---|---|---|
| A | `feedback` | `<YOUR_SERVER_IP>` | 300 |

After propagation, verify:

```bash
dig feedback.yourdomain.com
nslookup feedback.yourdomain.com
```

---

## Part 5 – Running the Application

Once DNS, Apache, and the database are configured, open:

```
http://feedback.local:8082/
```

You should see the feedback form at the top and the submissions table below.

---

## Prompts Used (AI Generation Log)

Below is the complete list of prompts used to generate this project with GitHub Copilot:

1. **Application scaffold**  
   *"Create a PHP + MySQL + Apache2 web application. Include at least one HTML form that writes data to a MySQL database and reads it back. Use mysqli for the database connection. The app should be a student feedback form with name, email, star rating 1–5, and a comment field."*

2. **Database SQL**  
   *"Generate MySQL CREATE DATABASE, CREATE TABLE, and sample INSERT statements for a student_feedback database with a feedback table containing id, name, email, rating, comment, and submitted_at columns. Also create a least-privilege appuser."*

3. **CSS styling**  
   *"Write a clean, modern CSS stylesheet for the student feedback app. Include card layout, alert messages for success/error, a star rating UI, a submit button with gradient, and a responsive data table."*

4. **Apache virtual host**  
   *"Generate a working Apache2 virtual host configuration file for Debian/Ubuntu. The virtual host must listen on port 8082 (not 80 or 443), use ServerName feedback.local, point DocumentRoot to /var/www/student-feedback/public, and include error/access logging."*

5. **ports.conf**  
   *"Generate an Apache2 ports.conf file that keeps the default Listen 80 and Listen 443 directives and adds Listen 8082 for a custom virtual host."*

6. **DNS setup**  
   *"Explain how to add a DNS A record for feedback.local pointing to a server IP, and how to verify it with dig and nslookup. Also include the /etc/hosts method for local testing."*

7. **README**  
   *"Write a comprehensive README.md for a PHP + MySQL + Apache2 student feedback project. Include project structure, database setup instructions, Apache virtual host deployment steps, DNS configuration, and a complete list of AI prompts used."*

---

## License

MIT – free to use for educational purposes.
