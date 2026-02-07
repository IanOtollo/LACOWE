# Deployment Guide: LACOWE Welfare MIS

This guide covers how to deploy the application as a **Web Application** (accessible to others) and optional steps for future **Mobile App** conversion.

## Option A: Local Network Deployment (Easiest)
Use this if you want colleagues in the same office/Wi-Fi to access the system from their laptops or phones.

1.  **Find your Laptop's IP Address:**
    *   Open Terminal (PowerShell) and run: `ipconfig`
    *   Look for **IPv4 Address** (e.g., `192.168.1.7` or `10.44.176.53`).
2.  **Configure XAMPP:**
    *   Ensure Apache is running.
    *   Ideally, your firewall should allow connection to Apache HTTP Server (Port 80/8080).
3.  **Access:**
    *   Other users can visit: `http://<YOUR_IP_ADDRESS>/lacowe-welfare-mis`
    *   Example: `http://192.168.1.7/lacowe-welfare-mis`

## Option B: Cloud Hosting (Professional)
To make it accessible via a real domain (e.g., `lacowe.com`):

1.  **Choose a Host:**
    *   Shared Hosting (HostGator, Bluehost) or VPS (DigitalOcean).
    *   Must support **PHP 8.0+** and **MySQL**.
2.  **Upload Files:**
    *   Use FTP (FileZilla) to upload `c:/xampp/htdocs/lacowe-welfare-mis` content to `public_html`.
3.  **Import Database:**
    *   Export your local database using PHPMyAdmin (`lacowe_db.sql`).
    *   Import it to the live server's database.
4.  **Update Config:**
    *   Edit `config/config.php` on the live server:
        ```php
        define('DB_HOST', 'localhost'); // Usually stays localhost
        define('DB_NAME', 'your_live_db_name');
        define('DB_USER', 'your_live_db_user');
        define('DB_PASS', 'your_live_db_password');
        define('APP_URL', 'http://yourdomain.com');
        ```

## Future: Converting to a Mobile App
Since you want to make this an "App" later, we have positioned the UI to be **Mobile-First**.

1.  **PWA (Progressive Web App):**
    *   We can add a `manifest.json` and service worker later.
    *   Users can "Add to Home Screen" and it looks/feels like an app.
2.  **Wrapper (Cordova/Capacitor):**
    *   We can wrap this PHP web app into an APK for Android using tools like Apache Cordova.

---
**Current Status:** The system is ready for **Option A (Local Network)** immediately.
