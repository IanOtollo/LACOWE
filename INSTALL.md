# LACOWE Welfare MIS - Installation Guide

## System Requirements

### Minimum Requirements
- Apache 2.4+ or Nginx 1.18+
- PHP 7.4+ (Recommended: PHP 8.0+)
- MySQL 5.7+ or MariaDB 10.3+
- 512MB RAM
- 100MB Disk Space

### PHP Extensions Required
- PDO
- pdo_mysql
- mysqli
- mbstring
- json
- session
- openssl

## Installation Steps

### Step 1: Download and Extract
Extract the system files to your web server directory:
- For XAMPP: `C:\xampp\htdocs\lacowe-welfare-mis`
- For WAMP: `C:\wamp64\www\lacowe-welfare-mis`
- For Linux: `/var/www/html/lacowe-welfare-mis`

### Step 2: Database Setup

#### Option A: Using phpMyAdmin
1. Open phpMyAdmin in your browser
2. Create a new database named `lacowe_welfare_mis`
3. Select the database
4. Click "Import" tab
5. Choose file `database/schema.sql`
6. Click "Go" to import

#### Option B: Using Command Line
```bash
# Login to MySQL
mysql -u root -p

# Create database
CREATE DATABASE lacowe_welfare_mis;

# Exit MySQL
exit;

# Import schema
mysql -u root -p lacowe_welfare_mis < database/schema.sql
```

### Step 3: Configure Database Connection
1. Open `config/config.php`
2. Update these lines with your database details:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'lacowe_welfare_mis');
define('DB_USER', 'root');
define('DB_PASS', 'your_mysql_password');
```

### Step 4: Set File Permissions (Linux/Mac)
```bash
cd /path/to/lacowe-welfare-mis
chmod 755 -R .
chmod 777 assets/uploads
```

### Step 5: Start Web Server

#### XAMPP Users
1. Start XAMPP Control Panel
2. Start Apache and MySQL services
3. Open browser and go to: `http://localhost/lacowe-welfare-mis`

#### WAMP Users
1. Start WAMP
2. Wait for icon to turn green
3. Open browser and go to: `http://localhost/lacowe-welfare-mis`

#### Linux/Mac Users
```bash
# Start Apache
sudo service apache2 start

# Start MySQL
sudo service mysql start
```

### Step 6: First Login
1. Navigate to: `http://localhost/lacowe-welfare-mis`
2. Login with default credentials:
   - Username: `admin`
   - Password: `Admin@123`

### Step 7: Security Setup (CRITICAL)
1. Change default admin password immediately
2. Update these settings in `config/config.php`:
```php
// Set to 0 in production
error_reporting(0);
ini_set('display_errors', 0);

// Enable HTTPS in production
ini_set('session.cookie_secure', 1);
```

## Troubleshooting

### Problem: Cannot connect to database
**Solution**: 
- Check MySQL is running
- Verify database credentials in config.php
- Ensure database exists

### Problem: Blank page or errors
**Solution**:
- Enable error reporting temporarily:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```
- Check PHP error log

### Problem: Login not working
**Solution**:
- Clear browser cookies
- Check session settings in php.ini
- Verify database has default admin user

### Problem: Permission denied
**Solution**:
```bash
chmod 755 -R /path/to/lacowe-welfare-mis
```

## Production Deployment Checklist

- [ ] Change default admin password
- [ ] Update database credentials
- [ ] Disable error display
- [ ] Enable HTTPS
- [ ] Set proper file permissions
- [ ] Enable database backups
- [ ] Configure firewall rules
- [ ] Update PHP to latest version
- [ ] Set up monitoring/logging

## Support

For technical support:
- Email: admin@lacowe.jkuat.ac.ke
- Phone: +254 XXX XXX XXX

## Database Backup

Create regular backups:
```bash
# Backup command
mysqldump -u root -p lacowe_welfare_mis > backup_$(date +%Y%m%d_%H%M%S).sql

# Restore command
mysql -u root -p lacowe_welfare_mis < backup_file.sql
```

## System Update

When updating the system:
1. Backup database first
2. Backup current files
3. Extract new version
4. Run any migration scripts
5. Test thoroughly before going live

---

**Installation completed! Welcome to LACOWE Welfare MIS**
