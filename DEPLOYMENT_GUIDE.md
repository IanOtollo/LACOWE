# Deployment Checklist & Database Migration

Follow these steps to move your local project to a live server.

## 1. Files Preparation
- [ ] Export your database (see below).
- [ ] Ensure all local changes are saved.
- [ ] Check if `config/config.php` has placeholders or your hosting details.

## 2. Database Migration (SQL)
To export your local database:
1. Open **phpMyAdmin** on your local machine (`http://localhost/phpmyadmin`).
2. Select `lacowe_welfare_mis`.
3. Click "Export" -> "Quick" -> "Go".
4. Save the `.sql` file.

To import to the live server:
1. Open the hosting **Control Panel**.
2. Go to **phpMyAdmin**.
3. Select your database (e.g., `if0_41092878_lacowe_mis`) from the left sidebar.
4. Click **"Import"**.
5. Upload your `database/schema.sql` file.

> [!NOTE]
> If you see an error about `DROP DATABASE`, ensure you have removed the `DROP`, `CREATE`, and `USE` lines from the top of your `.sql` file. I have already fixed this for you in the local `database/schema.sql` file.

## 3. Configuration Update
Update `config/config.php` on the server with:
- `DB_HOST`: Host provided by your host (e.g., `sqlXXX.infinityfree.com`).
- `DB_NAME`: Your live database name.
- `DB_USER`: Your live database username.
- `DB_PASS`: Your live database password.
- `APP_URL`: Your domain (e.g., `https://lacowe.infinityfreeapp.com`).

## 4. Final Tests
- [ ] Test the Login page.
- [ ] Check if images/icons load correctly.
- [ ] Verify that transactions and member records are visible.
- [ ] Verify PWA installation (Look for "Add to Home Screen" in browser menu).
