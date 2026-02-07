# FreeMySQLHosting Guide

If InfinityFree's built-in MySQL is slow or restricted, you can use a dedicated free MySQL host.

## Step 1: Sign Up
1. Visit [FreeMySQLHosting.net](https://www.freemysqlhosting.net/).
2. Create a free account.

## Step 2: Create Database
1. In your dashboard, click **"Start your free database"**.
2. Select a location (e.g., Europe or US).
3. You will receive an email with your database credentials:
   - **Host**
   - **Database Name**
   - **Username**
   - **Password**
   - **Port** (usually 3306)

## Step 3: Update MIS Config
In `config/config.php`, use the remote host instead of `localhost`:
```php
define('DB_HOST', 'sqlX.freemysqlhosting.net');
define('DB_NAME', 'your_db_name');
define('DB_USER', 'your_db_user');
define('DB_PASS', 'your_db_password');
```

## Step 4: Remote Access
Note: Ensure your web host (InfinityFree) allows remote MySQL connections. If not, use InfinityFree's local MySQL instead.
