# Railway.app Guide - Modern Professional Alternative

Railway is a cloud platform that is extremely fast and professional. It uses "credits" ($5 free trial) which lasts a long time for small projects.

## Step 1: Sign Up
1. Visit [Railway.app](https://railway.app/) and sign up with GitHub or Email.

## Step 2: Create a New Project
1. Click **"New Project"**.
2. Choose **"Provision MySQL"**. This gives you an instant database.

## Step 3: Add PHP Side
1. You can deploy directly from a GitHub repository.
2. If you don't have GitHub, you can use the Railway CLI to `railway up`.

## Step 4: Environment Variables
Instead of editing `config/config.php` directly, you can use Railway environment variables:
- `MYSQLHOST`
- `MYSQLDATABASE`
- `MYSQLUSER`
- `MYSQLPASSWORD`
- `MYSQLPORT`

## Step 5: Import Data
Railway provides a connection string you can use with any SQL client (like HeidiSQL or MySQL Workbench) to run your `schema.sql`.

## Why use Railway?
- **No DNS Wait**: Your app is live at a random `railway.app` URL immediately.
- **Modern**: Excellent logs, metrics, and deployment history.
- **Reliable**: much more stable than "free shared hosting".
