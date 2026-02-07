-- LACOWE MIS - PostgreSQL Schema (for Supabase)

-- 1. ENUMS (PostgreSQL requires explicit ENUM types)
CREATE TYPE gender_type AS ENUM ('Male', 'Female', 'Other');
CREATE TYPE employment_status_type AS ENUM ('Active', 'Retired', 'Resigned');
CREATE TYPE membership_status_type AS ENUM ('Active', 'Suspended', 'Inactive');
CREATE TYPE account_type_type AS ENUM ('Savings', 'Shares', 'Deposits');
CREATE TYPE account_status_type AS ENUM ('Active', 'Dormant', 'Closed');
CREATE TYPE transaction_type_type AS ENUM ('Deposit', 'Withdrawal', 'Transfer', 'Interest', 'Fee', 'Loan Disbursement', 'Loan Repayment');
CREATE TYPE loan_type_type AS ENUM ('Emergency', 'Development', 'School Fees', 'Business', 'Personal');
CREATE TYPE application_status_type AS ENUM ('Pending', 'Under Review', 'Approved', 'Rejected', 'Cancelled');
CREATE TYPE loan_status_type AS ENUM ('Active', 'Fully Paid', 'Defaulted', 'Written Off');
CREATE TYPE payment_method_type AS ENUM ('Cash', 'Bank Transfer', 'Mobile Money', 'Salary Deduction');
CREATE TYPE contribution_type_type AS ENUM ('Monthly Savings', 'Shares', 'Registration Fee', 'Welfare Fund');
CREATE TYPE ledger_account_type AS ENUM ('Asset', 'Liability', 'Equity', 'Revenue', 'Expense');
CREATE TYPE journal_status_type AS ENUM ('Draft', 'Posted', 'Reversed');

-- 2. TABLES

CREATE TABLE roles (
    role_id SERIAL PRIMARY KEY,
    role_name VARCHAR(50) NOT NULL UNIQUE,
    role_description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE users (
    user_id SERIAL PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role_id INT NOT NULL REFERENCES roles(role_id),
    is_active BOOLEAN DEFAULT TRUE,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE members (
    member_id SERIAL PRIMARY KEY,
    user_id INT NOT NULL UNIQUE REFERENCES users(user_id) ON DELETE CASCADE,
    member_number VARCHAR(20) NOT NULL UNIQUE,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    id_number VARCHAR(20) NOT NULL UNIQUE,
    phone_number VARCHAR(15) NOT NULL,
    email VARCHAR(100),
    date_of_birth DATE,
    gender gender_type,
    address TEXT,
    city VARCHAR(50),
    postal_code VARCHAR(10),
    employment_status employment_status_type DEFAULT 'Active',
    department VARCHAR(100),
    payroll_number VARCHAR(20),
    date_joined DATE NOT NULL,
    membership_status membership_status_type DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE accounts (
    account_id SERIAL PRIMARY KEY,
    member_id INT NOT NULL REFERENCES members(member_id) ON DELETE CASCADE,
    account_number VARCHAR(20) NOT NULL UNIQUE,
    account_type account_type_type NOT NULL,
    balance DECIMAL(15, 2) DEFAULT 0.00 CHECK (balance >= 0),
    interest_rate DECIMAL(5, 2) DEFAULT 0.00,
    date_opened DATE NOT NULL,
    account_status account_status_type DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE transactions (
    transaction_id SERIAL PRIMARY KEY,
    account_id INT NOT NULL REFERENCES accounts(account_id) ON DELETE CASCADE,
    transaction_type transaction_type_type NOT NULL,
    amount DECIMAL(15, 2) NOT NULL CHECK (amount > 0),
    transaction_date TIMESTAMP NOT NULL,
    description TEXT,
    reference_number VARCHAR(50) NOT NULL UNIQUE,
    processed_by INT NOT NULL REFERENCES users(user_id),
    balance_before DECIMAL(15, 2) NOT NULL,
    balance_after DECIMAL(15, 2) NOT NULL,
    status VARCHAR(20) DEFAULT 'Completed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Note: Simplified some features for basic Supabase deployment
-- Remaining tables (loans, repayments, etc.) follow similar SERIAL/ENUM/TIMESTAMP patterns.
