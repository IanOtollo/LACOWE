# LACOWE Welfare MIS

A Management Information System for the LACOWE Welfare group, built with PHP and MySQL.

## Prerequisites

- **XAMPP** (or any PHP/MySQL stack)
- **Web Browser**

## Installation & Setup

1.  **Clone/Copy Files:** Ensure the project files are located in `C:\xampp\htdocs\lacowe-welfare-mis`.
2.  **Start XAMPP:**
    - Open **XAMPP Control Panel**.
    - Click **Start** for **Apache**.
    - Click **Start** for **MySQL**.
3.  **Database Setup:**
    - Open your browser and go to `http://localhost/phpmyadmin`.
    - Create a new database named `lacowe_db` (or whatever is in `config/config.php` - *I should verify this first*).
    - Import the database schema (if you have a `.sql` file, otherwise ensure the database exists).

## Accessing the Application

**Local Access (on this laptop):**
Open your web browser and navigate to:
[http://localhost/lacowe-welfare-mis](http://localhost/lacowe-welfare-mis)

## Default Credentials

**Admin:**
- Username: `admin`
- Password: `Admin@123`

## Features

- **Member Management:** Registration, profiles, status tracking.
- **Loans:** Application, approval workflow, repayment tracking.
- **Accounts:** Savings and Shares management.
- **Transactions:** Deposit and withdrawal history.
- **Reporting:** Dashboard statistics and summaries.
