# 000webhost Guide - 100% FREE Fast Alternative

000webhost is a **100% free** popular alternative that often has faster DNS propagation for its subdomains (`.000webhostapp.com`).

## Step 1: Sign Up
1. Visit [000webhost.com](https://www.000webhost.com/) and sign up for a free account.
2. Verify your email and log in.

## Step 2: Create Your Site
1. Click **"Create New Site"**.
2. Give your site a name (e.g., `lacowe-mis`).
3. Your URL will be `https://lacowe-mis.000webhostapp.com`. This is usually active **instantly**.

## Step 3: Setup Database
1. In the 000webhost dashboard, go to **Tools** > **Database Manager**.
2. Click **"New Database"**.
3. Set the database name, username, and password.
4. **Important**: 000webhost prefixes names (e.g., `id123456_lacowe_mis`).
5. Click **"Manage"** > **"phpMyAdmin"** to import your `database/schema.sql`.

## Step 4: Upload Files
1. Go to **Tools** > **File Manager**.
2. Click **"Upload Files"** to open the browser-based manager.
3. Open the `public_html` folder (this is the equivalent of `/htdocs`).
4. Upload all your project files here.

## Step 5: Config Update
Edit `config/config.php` on the server:
- `DB_HOST`: Always `localhost` for 000webhost.
- `DB_NAME`: Your prefixed name (e.g., `id123456_lacowe_mis`).
- `DB_USER`: Your prefixed username (e.g., `id123456_admin`).
- `DB_PASS`: The password you set.

## Why use 000webhost?
- **Speed**: Subdomains are usually ready in minutes, not days.
- **SSL**: They provide free HTTPS automatically on their subdomains.
- **Easy Interface**: The dashboard is very modern compared to cPanel.
