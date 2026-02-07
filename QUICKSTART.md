# LACOWE Welfare MIS - Quick Start Guide

## 🚀 5-Minute Setup

### Step 1: Extract Files (30 seconds)
Extract `lacowe-welfare-mis` folder to your web server directory:
- **XAMPP**: `C:\xampp\htdocs\`
- **WAMP**: `C:\wamp64\www\`
- **Linux**: `/var/www/html/`

### Step 2: Create Database (1 minute)
Open phpMyAdmin and import `database/schema.sql`:
1. Create database: `lacowe_welfare_mis`
2. Select database
3. Click "Import" tab
4. Choose `database/schema.sql`
5. Click "Go"

### Step 3: Configure (30 seconds)
Edit `config/config.php`:
```php
define('DB_USER', 'root');
define('DB_PASS', 'your_mysql_password');
```

### Step 4: Access System (30 seconds)
1. Open browser: `http://localhost/lacowe-welfare-mis`
2. Login:
   - Username: `admin`
   - Password: `Admin@123`

### Step 5: Security (2 minutes)
1. Change admin password immediately
2. Create first member
3. Test transactions

## ✅ That's It!

### First Actions:
1. **Register a Member**: Members → Register New Member
2. **Process Deposit**: Transactions → Enter details → Process
3. **Apply for Loan**: (as member) Apply for Loan → Fill form
4. **Approve Loan**: (as admin) Loans → Approve

### Common Tasks:

**Add New Member:**
```
Members → + Register New Member → Fill Form → Submit
```

**Process Transaction:**
```
Transactions → Enter Account ID + Amount → Process
```

**Approve Loan:**
```
Loans → Filter: Pending → Select Application → Approve
```

## 🆘 Need Help?

**Database Connection Error?**
- Check MySQL is running
- Verify credentials in `config/config.php`

**Login Not Working?**
- Clear browser cookies
- Check database imported correctly

**Can't See Pages?**
- Check user role permissions
- Verify logged in correctly

## 📚 Full Documentation

- **Complete Guide**: USER_GUIDE.md
- **Installation**: INSTALL.md
- **System Info**: README.md

---

**Happy Managing! 🎉**
