# InfinityFree Hosting Guide for LACOWE Welfare MIS

InfinityFree provides free web hosting with PHP and MySQL support, making it ideal for this project.

## Step 1: Create an Account
1. Visit [InfinityFree](https://www.infinityfree.com/) and sign up for a free account.
2. Verify your email address and log in to the client area.

## Step 2: Create a Hosting Account
1. Click on **"Create Account"**.
2. Choose a domain type (Subdomain is free, e.g., `lacowe.infinityfreeapp.com`).
3. Set an account label and password.
4. Wait for the account to be "Active" (usually takes a few minutes).

## Step 3: Configure Database
1. Go to your **Control Panel** (vPanel).
2. Look for **"MySQL Databases"**.
3. Create a new database named `lacowe_mis`.
4. Note down the following details from the MySQL page:
   - **DB Host** (looks like `sqlXXX.infinityfree.com`)
   - **DB Name** (looks like `ifXXX_lacowe_mis`)
   - **DB User** (looks like `ifXXX`)
   - **DB Password** (found in your InfinityFree client area under "Account Details")

## Step 4: Upload Files
1. Use an FTP client like **FileZilla** or the online **File Manager** in vPanel.
2. Upload all files from your local `lacowe-welfare-mis` folder to the `/htdocs` folder on the server.

## Step 5: Update Configuration
1. Open `config/config.php` on the server.
2. Update the database credentials with the details from Step 3:
   ```php
   define('DB_HOST', 'your_db_host');
   define('DB_NAME', 'your_db_name');
   define('DB_USER', 'your_db_user');
   define('DB_PASS', 'your_db_password');
   ```

## Step 6: Import Database
1. Go to **"phpMyAdmin"** in the Control Panel.
2. Select your new database.
3. Click **"Import"** and upload the `database/lacowe_welfare_mis.sql` file.
