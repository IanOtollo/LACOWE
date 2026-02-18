-- ============================================
-- LACOWE WELFARE GROUP MIS DATABASE SCHEMA
-- ============================================
-- Author: Esther Wambui
-- Institution: JKUAT - SCT121-0518/2024
-- Date: 2026
-- ============================================

-- (Database creation lines removed for compatibility with shared hosting)

-- ============================================
-- 1. ROLES TABLE
-- ============================================
CREATE TABLE roles (
    role_id INT AUTO_INCREMENT PRIMARY KEY,
    role_name VARCHAR(50) NOT NULL UNIQUE,
    role_description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================
-- 2. USERS TABLE
-- ============================================
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role_id INT NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(role_id) ON DELETE RESTRICT,
    INDEX idx_username (username),
    INDEX idx_email (email)
) ENGINE=InnoDB;

-- ============================================
-- 3. MEMBERS TABLE
-- ============================================
CREATE TABLE members (
    member_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    member_number VARCHAR(20) NOT NULL UNIQUE,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    id_number VARCHAR(20) NOT NULL UNIQUE,
    phone_number VARCHAR(15) NOT NULL,
    email VARCHAR(100),
    date_of_birth DATE,
    gender ENUM('Male', 'Female', 'Other'),
    address TEXT,
    city VARCHAR(50),
    postal_code VARCHAR(10),
    employment_status ENUM('Active', 'Retired', 'Resigned') DEFAULT 'Active',
    department VARCHAR(100),
    payroll_number VARCHAR(20),
    date_joined DATE NOT NULL,
    membership_status ENUM('Active', 'Suspended', 'Inactive') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_member_number (member_number),
    INDEX idx_id_number (id_number),
    INDEX idx_membership_status (membership_status)
) ENGINE=InnoDB;

-- ============================================
-- 4. ACCOUNTS TABLE
-- ============================================
CREATE TABLE accounts (
    account_id INT AUTO_INCREMENT PRIMARY KEY,
    member_id INT NOT NULL,
    account_number VARCHAR(20) NOT NULL UNIQUE,
    account_type ENUM('Savings', 'Shares', 'Deposits') NOT NULL,
    balance DECIMAL(15, 2) DEFAULT 0.00,
    interest_rate DECIMAL(5, 2) DEFAULT 0.00,
    date_opened DATE NOT NULL,
    account_status ENUM('Active', 'Dormant', 'Closed') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (member_id) REFERENCES members(member_id) ON DELETE CASCADE,
    INDEX idx_account_number (account_number),
    INDEX idx_member_id (member_id),
    CHECK (balance >= 0)
) ENGINE=InnoDB;

-- ============================================
-- 5. TRANSACTIONS TABLE
-- ============================================
CREATE TABLE transactions (
    transaction_id INT AUTO_INCREMENT PRIMARY KEY,
    account_id INT NOT NULL,
    transaction_type ENUM('Deposit', 'Withdrawal', 'Transfer', 'Interest', 'Fee', 'Loan Disbursement', 'Loan Repayment') NOT NULL,
    amount DECIMAL(15, 2) NOT NULL,
    transaction_date DATETIME NOT NULL,
    description TEXT,
    reference_number VARCHAR(50) NOT NULL UNIQUE,
    processed_by INT NOT NULL,
    balance_before DECIMAL(15, 2) NOT NULL,
    balance_after DECIMAL(15, 2) NOT NULL,
    status ENUM('Pending', 'Completed', 'Failed', 'Reversed') DEFAULT 'Completed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (account_id) REFERENCES accounts(account_id) ON DELETE CASCADE,
    FOREIGN KEY (processed_by) REFERENCES users(user_id) ON DELETE RESTRICT,
    INDEX idx_transaction_date (transaction_date),
    INDEX idx_account_id (account_id),
    INDEX idx_reference_number (reference_number),
    CHECK (amount > 0)
) ENGINE=InnoDB;

-- ============================================
-- 6. LOAN APPLICATIONS TABLE
-- ============================================
CREATE TABLE loan_applications (
    application_id INT AUTO_INCREMENT PRIMARY KEY,
    member_id INT NOT NULL,
    loan_type ENUM('Emergency', 'Development', 'School Fees', 'Business', 'Personal') NOT NULL,
    amount_requested DECIMAL(15, 2) NOT NULL,
    loan_purpose TEXT NOT NULL,
    repayment_period INT NOT NULL COMMENT 'In months',
    application_date DATE NOT NULL,
    guarantor1_id INT,
    guarantor2_id INT,
    collateral_description TEXT,
    application_status ENUM('Pending', 'Under Review', 'Approved', 'Rejected', 'Cancelled') DEFAULT 'Pending',
    reviewed_by INT,
    review_date DATE,
    review_comments TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (member_id) REFERENCES members(member_id) ON DELETE CASCADE,
    FOREIGN KEY (guarantor1_id) REFERENCES members(member_id) ON DELETE SET NULL,
    FOREIGN KEY (guarantor2_id) REFERENCES members(member_id) ON DELETE SET NULL,
    FOREIGN KEY (reviewed_by) REFERENCES users(user_id) ON DELETE SET NULL,
    INDEX idx_application_status (application_status),
    INDEX idx_member_id (member_id),
    CHECK (amount_requested > 0),
    CHECK (repayment_period > 0)
) ENGINE=InnoDB;

-- ============================================
-- 7. LOANS TABLE
-- ============================================
CREATE TABLE loans (
    loan_id INT AUTO_INCREMENT PRIMARY KEY,
    application_id INT NOT NULL UNIQUE,
    member_id INT NOT NULL,
    loan_number VARCHAR(20) NOT NULL UNIQUE,
    principal_amount DECIMAL(15, 2) NOT NULL,
    interest_rate DECIMAL(5, 2) NOT NULL,
    total_amount DECIMAL(15, 2) NOT NULL,
    monthly_installment DECIMAL(15, 2) NOT NULL,
    repayment_period INT NOT NULL COMMENT 'In months',
    disbursement_date DATE NOT NULL,
    maturity_date DATE NOT NULL,
    amount_paid DECIMAL(15, 2) DEFAULT 0.00,
    balance DECIMAL(15, 2) NOT NULL,
    loan_status ENUM('Active', 'Fully Paid', 'Defaulted', 'Written Off') DEFAULT 'Active',
    disbursed_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (application_id) REFERENCES loan_applications(application_id) ON DELETE RESTRICT,
    FOREIGN KEY (member_id) REFERENCES members(member_id) ON DELETE CASCADE,
    FOREIGN KEY (disbursed_by) REFERENCES users(user_id) ON DELETE RESTRICT,
    INDEX idx_loan_number (loan_number),
    INDEX idx_member_id (member_id),
    INDEX idx_loan_status (loan_status),
    CHECK (principal_amount > 0),
    CHECK (interest_rate >= 0),
    CHECK (balance >= 0)
) ENGINE=InnoDB;

-- ============================================
-- 8. LOAN REPAYMENTS TABLE
-- ============================================
CREATE TABLE loan_repayments (
    repayment_id INT AUTO_INCREMENT PRIMARY KEY,
    loan_id INT NOT NULL,
    payment_date DATE NOT NULL,
    amount_paid DECIMAL(15, 2) NOT NULL,
    principal_paid DECIMAL(15, 2) NOT NULL,
    interest_paid DECIMAL(15, 2) NOT NULL,
    balance_after DECIMAL(15, 2) NOT NULL,
    payment_method ENUM('Cash', 'Bank Transfer', 'Mobile Money', 'Salary Deduction') NOT NULL,
    receipt_number VARCHAR(50) NOT NULL UNIQUE,
    processed_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (loan_id) REFERENCES loans(loan_id) ON DELETE CASCADE,
    FOREIGN KEY (processed_by) REFERENCES users(user_id) ON DELETE RESTRICT,
    INDEX idx_loan_id (loan_id),
    INDEX idx_payment_date (payment_date),
    INDEX idx_receipt_number (receipt_number),
    CHECK (amount_paid > 0),
    CHECK (balance_after >= 0)
) ENGINE=InnoDB;

-- ============================================
-- 9. CONTRIBUTIONS TABLE
-- ============================================
CREATE TABLE contributions (
    contribution_id INT AUTO_INCREMENT PRIMARY KEY,
    member_id INT NOT NULL,
    contribution_type ENUM('Monthly Savings', 'Shares', 'Registration Fee', 'Welfare Fund') NOT NULL,
    amount DECIMAL(15, 2) NOT NULL,
    contribution_date DATE NOT NULL,
    payment_method ENUM('Cash', 'Bank Transfer', 'Mobile Money', 'Salary Deduction') NOT NULL,
    receipt_number VARCHAR(50) NOT NULL UNIQUE,
    processed_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (member_id) REFERENCES members(member_id) ON DELETE CASCADE,
    FOREIGN KEY (processed_by) REFERENCES users(user_id) ON DELETE RESTRICT,
    INDEX idx_member_id (member_id),
    INDEX idx_contribution_date (contribution_date),
    INDEX idx_receipt_number (receipt_number),
    CHECK (amount > 0)
) ENGINE=InnoDB;

-- ============================================
-- 10. GENERAL LEDGER TABLE
-- ============================================
CREATE TABLE general_ledger (
    ledger_id INT AUTO_INCREMENT PRIMARY KEY,
    account_code VARCHAR(20) NOT NULL,
    account_name VARCHAR(100) NOT NULL,
    account_type ENUM('Asset', 'Liability', 'Equity', 'Revenue', 'Expense') NOT NULL,
    debit DECIMAL(15, 2) DEFAULT 0.00,
    credit DECIMAL(15, 2) DEFAULT 0.00,
    balance DECIMAL(15, 2) DEFAULT 0.00,
    transaction_date DATE NOT NULL,
    description TEXT,
    reference_number VARCHAR(50),
    posted_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (posted_by) REFERENCES users(user_id) ON DELETE RESTRICT,
    INDEX idx_account_code (account_code),
    INDEX idx_transaction_date (transaction_date)
) ENGINE=InnoDB;

-- ============================================
-- 11. JOURNAL ENTRIES TABLE
-- ============================================
CREATE TABLE journal_entries (
    entry_id INT AUTO_INCREMENT PRIMARY KEY,
    entry_number VARCHAR(20) NOT NULL UNIQUE,
    entry_date DATE NOT NULL,
    description TEXT NOT NULL,
    total_debit DECIMAL(15, 2) NOT NULL,
    total_credit DECIMAL(15, 2) NOT NULL,
    created_by INT NOT NULL,
    approved_by INT,
    approval_date DATE,
    status ENUM('Draft', 'Posted', 'Reversed') DEFAULT 'Draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE RESTRICT,
    FOREIGN KEY (approved_by) REFERENCES users(user_id) ON DELETE SET NULL,
    INDEX idx_entry_number (entry_number),
    INDEX idx_entry_date (entry_date),
    CHECK (total_debit = total_credit)
) ENGINE=InnoDB;

-- ============================================
-- 12. AUDIT LOGS TABLE
-- ============================================
CREATE TABLE audit_logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    table_name VARCHAR(50) NOT NULL,
    record_id INT,
    old_values TEXT,
    new_values TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at),
    INDEX idx_table_name (table_name)
) ENGINE=InnoDB;

-- ============================================
-- 13. SYSTEM SETTINGS TABLE
-- ============================================
CREATE TABLE system_settings (
    setting_id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(50) NOT NULL UNIQUE,
    setting_value TEXT,
    setting_description TEXT,
    updated_by INT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (updated_by) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================
-- 14. BANK ACCOUNTS TABLE
-- ============================================
CREATE TABLE bank_accounts (
    bank_account_id INT AUTO_INCREMENT PRIMARY KEY,
    member_id INT NOT NULL,
    bank_name VARCHAR(100) NOT NULL,
    account_name VARCHAR(100) NOT NULL,
    account_number VARCHAR(50) NOT NULL,
    branch_name VARCHAR(100),
    swift_code VARCHAR(20),
    is_verified TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (member_id) REFERENCES members(member_id) ON DELETE CASCADE,
    INDEX idx_member_id (member_id)
) ENGINE=InnoDB;

-- ============================================
-- INSERT DEFAULT DATA
-- ============================================

-- Insert Roles
INSERT INTO roles (role_name, role_description) VALUES
('Super Admin', 'Full system access and configuration'),
('Administrator', 'Manage members, process transactions, generate reports'),
('Management Committee', 'Approve loans, review financial reports'),
('Member', 'View own account, apply for loans, make contributions');

-- Insert Default Super Admin User
-- Password: Admin@123 (hashed with bcrypt)
INSERT INTO users (username, email, password_hash, role_id) VALUES
('admin', 'admin@lacowe.jkuat.ac.ke', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1);

-- Insert System Settings
INSERT INTO system_settings (setting_key, setting_value, setting_description) VALUES
('system_name', 'LACOWE Welfare MIS', 'Name of the welfare management system'),
('organization_name', 'Lacowe Welfare Group', 'Name of the welfare organization'),
('default_interest_rate', '12.00', 'Default annual interest rate for loans (%)'),
('minimum_savings', '1000.00', 'Minimum savings balance required'),
('registration_fee', '2000.00', 'One-time member registration fee'),
('monthly_contribution', '1000.00', 'Required monthly contribution'),
('loan_processing_fee', '2.00', 'Loan processing fee percentage'),
('max_loan_amount', '500000.00', 'Maximum loan amount'),
('currency', 'KES', 'System currency'),
('financial_year_start', '01-01', 'Financial year start date (MM-DD)');

-- ============================================
-- CREATE VIEWS FOR REPORTING
-- ============================================

-- Member Account Summary View
CREATE VIEW vw_member_account_summary AS
SELECT 
    m.member_id,
    m.member_number,
    CONCAT(m.first_name, ' ', m.last_name) AS full_name,
    m.phone_number,
    m.membership_status,
    COUNT(DISTINCT a.account_id) AS total_accounts,
    SUM(a.balance) AS total_balance,
    COUNT(DISTINCT l.loan_id) AS active_loans,
    SUM(CASE WHEN l.loan_status = 'Active' THEN l.balance ELSE 0 END) AS total_loan_balance
FROM members m
LEFT JOIN accounts a ON m.member_id = a.member_id
LEFT JOIN loans l ON m.member_id = l.member_id AND l.loan_status = 'Active'
GROUP BY m.member_id;

-- Loan Portfolio Summary View
CREATE VIEW vw_loan_portfolio AS
SELECT 
    l.loan_id,
    l.loan_number,
    m.member_number,
    CONCAT(m.first_name, ' ', m.last_name) AS member_name,
    l.principal_amount,
    l.interest_rate,
    l.total_amount,
    l.amount_paid,
    l.balance,
    l.monthly_installment,
    l.disbursement_date,
    l.maturity_date,
    l.loan_status,
    DATEDIFF(l.maturity_date, CURDATE()) AS days_to_maturity
FROM loans l
INNER JOIN members m ON l.member_id = m.member_id;

-- Transaction Summary View
CREATE VIEW vw_transaction_summary AS
SELECT 
    t.transaction_id,
    t.reference_number,
    m.member_number,
    CONCAT(m.first_name, ' ', m.last_name) AS member_name,
    a.account_number,
    a.account_type,
    t.transaction_type,
    t.amount,
    t.transaction_date,
    t.status,
    CONCAT(u.username) AS processed_by
FROM transactions t
INNER JOIN accounts a ON t.account_id = a.account_id
INNER JOIN members m ON a.member_id = m.member_id
INNER JOIN users u ON t.processed_by = u.user_id;

-- ============================================
-- CREATE STORED PROCEDURES
-- ============================================

DELIMITER //

-- Procedure to process deposit
CREATE PROCEDURE sp_process_deposit(
    IN p_account_id INT,
    IN p_amount DECIMAL(15, 2),
    IN p_description TEXT,
    IN p_processed_by INT,
    OUT p_transaction_id INT,
    OUT p_reference_number VARCHAR(50)
)
BEGIN
    DECLARE v_balance_before DECIMAL(15, 2);
    DECLARE v_balance_after DECIMAL(15, 2);
    DECLARE v_ref_number VARCHAR(50);
    
    -- Start transaction
    START TRANSACTION;
    
    -- Get current balance
    SELECT balance INTO v_balance_before FROM accounts WHERE account_id = p_account_id FOR UPDATE;
    
    -- Calculate new balance
    SET v_balance_after = v_balance_before + p_amount;
    
    -- Generate reference number
    SET v_ref_number = CONCAT('DEP-', DATE_FORMAT(NOW(), '%Y%m%d'), '-', LPAD(p_account_id, 6, '0'), '-', UNIX_TIMESTAMP());
    
    -- Insert transaction
    INSERT INTO transactions (account_id, transaction_type, amount, transaction_date, description, reference_number, processed_by, balance_before, balance_after, status)
    VALUES (p_account_id, 'Deposit', p_amount, NOW(), p_description, v_ref_number, p_processed_by, v_balance_before, v_balance_after, 'Completed');
    
    SET p_transaction_id = LAST_INSERT_ID();
    SET p_reference_number = v_ref_number;
    
    -- Update account balance
    UPDATE accounts SET balance = v_balance_after WHERE account_id = p_account_id;
    
    COMMIT;
END//

-- Procedure to process withdrawal
CREATE PROCEDURE sp_process_withdrawal(
    IN p_account_id INT,
    IN p_amount DECIMAL(15, 2),
    IN p_description TEXT,
    IN p_processed_by INT,
    OUT p_transaction_id INT,
    OUT p_reference_number VARCHAR(50),
    OUT p_error_message VARCHAR(255)
)
BEGIN
    DECLARE v_balance_before DECIMAL(15, 2);
    DECLARE v_balance_after DECIMAL(15, 2);
    DECLARE v_ref_number VARCHAR(50);
    
    SET p_error_message = NULL;
    
    -- Start transaction
    START TRANSACTION;
    
    -- Get current balance
    SELECT balance INTO v_balance_before FROM accounts WHERE account_id = p_account_id FOR UPDATE;
    
    -- Check sufficient balance
    IF v_balance_before < p_amount THEN
        SET p_error_message = 'Insufficient balance';
        ROLLBACK;
    ELSE
        -- Calculate new balance
        SET v_balance_after = v_balance_before - p_amount;
        
        -- Generate reference number
        SET v_ref_number = CONCAT('WTH-', DATE_FORMAT(NOW(), '%Y%m%d'), '-', LPAD(p_account_id, 6, '0'), '-', UNIX_TIMESTAMP());
        
        -- Insert transaction
        INSERT INTO transactions (account_id, transaction_type, amount, transaction_date, description, reference_number, processed_by, balance_before, balance_after, status)
        VALUES (p_account_id, 'Withdrawal', p_amount, NOW(), p_description, v_ref_number, p_processed_by, v_balance_before, v_balance_after, 'Completed');
        
        SET p_transaction_id = LAST_INSERT_ID();
        SET p_reference_number = v_ref_number;
        
        -- Update account balance
        UPDATE accounts SET balance = v_balance_after WHERE account_id = p_account_id;
        
        COMMIT;
    END IF;
END//

-- Procedure to process loan repayment
CREATE PROCEDURE sp_process_loan_repayment(
    IN p_loan_id INT,
    IN p_amount DECIMAL(15, 2),
    IN p_payment_method VARCHAR(20),
    IN p_processed_by INT,
    OUT p_repayment_id INT,
    OUT p_receipt_number VARCHAR(50),
    OUT p_error_message VARCHAR(255)
)
BEGIN
    DECLARE v_loan_balance DECIMAL(15, 2);
    DECLARE v_interest_rate DECIMAL(5, 2);
    DECLARE v_principal_paid DECIMAL(15, 2);
    DECLARE v_interest_paid DECIMAL(15, 2);
    DECLARE v_balance_after DECIMAL(15, 2);
    DECLARE v_receipt_number VARCHAR(50);
    
    SET p_error_message = NULL;
    
    -- Start transaction
    START TRANSACTION;
    
    -- Get loan details
    SELECT balance, interest_rate INTO v_loan_balance, v_interest_rate 
    FROM loans WHERE loan_id = p_loan_id FOR UPDATE;
    
    -- Check if loan exists and is active
    IF v_loan_balance IS NULL THEN
        SET p_error_message = 'Loan not found';
        ROLLBACK;
    ELSEIF v_loan_balance = 0 THEN
        SET p_error_message = 'Loan already fully paid';
        ROLLBACK;
    ELSE
        -- Calculate interest and principal portions (simplified)
        SET v_interest_paid = (p_amount * 0.3); -- 30% to interest
        SET v_principal_paid = p_amount - v_interest_paid;
        
        -- Calculate balance after payment
        SET v_balance_after = v_loan_balance - p_amount;
        IF v_balance_after < 0 THEN
            SET v_balance_after = 0;
        END IF;
        
        -- Generate receipt number
        SET v_receipt_number = CONCAT('LRP-', DATE_FORMAT(NOW(), '%Y%m%d'), '-', LPAD(p_loan_id, 6, '0'), '-', UNIX_TIMESTAMP());
        
        -- Insert repayment record
        INSERT INTO loan_repayments (loan_id, payment_date, amount_paid, principal_paid, interest_paid, balance_after, payment_method, receipt_number, processed_by)
        VALUES (p_loan_id, CURDATE(), p_amount, v_principal_paid, v_interest_paid, v_balance_after, p_payment_method, v_receipt_number, p_processed_by);
        
        SET p_repayment_id = LAST_INSERT_ID();
        SET p_receipt_number = v_receipt_number;
        
        -- Update loan balance and amount paid
        UPDATE loans 
        SET balance = v_balance_after, 
            amount_paid = amount_paid + p_amount,
            loan_status = CASE WHEN v_balance_after = 0 THEN 'Fully Paid' ELSE 'Active' END
        WHERE loan_id = p_loan_id;
        
        COMMIT;
    END IF;
END//

DELIMITER ;

-- ============================================
-- END OF SCHEMA
-- ============================================
