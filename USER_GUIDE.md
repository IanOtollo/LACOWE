# LACOWE Welfare MIS - User Guide

## Table of Contents
1. [Introduction](#introduction)
2. [User Roles](#user-roles)
3. [Getting Started](#getting-started)
4. [Member Management](#member-management)
5. [Account Management](#account-management)
6. [Loan Management](#loan-management)
7. [Transaction Processing](#transaction-processing)
8. [Reports and Analytics](#reports-and-analytics)
9. [User Profile](#user-profile)

---

## Introduction

LACOWE Welfare MIS is a comprehensive system designed to manage all welfare group operations including member registration, savings accounts, loan processing, and financial reporting.

### System Access
- URL: `http://your-domain/lacowe-welfare-mis`
- Default Admin: Username: `admin` | Password: `Admin@123`

**⚠️ IMPORTANT**: Change default password immediately after first login!

---

## User Roles

### 1. Super Admin
**Capabilities:**
- Full system access
- User management
- System configuration
- All administrative functions

### 2. Administrator
**Capabilities:**
- Manage member records
- Process transactions
- Generate reports
- Loan processing

### 3. Management Committee
**Capabilities:**
- Approve/reject loans
- Review financial reports
- Make policy decisions
- Monitor performance

### 4. Member
**Capabilities:**
- View personal account
- Apply for loans
- View transaction history
- Update profile

---

## Getting Started

### First Login
1. Navigate to the system URL
2. Enter username and password
3. Click "Sign In"
4. You'll be directed to your dashboard

### Dashboard Overview

#### Admin Dashboard Shows:
- Total members count
- Active members
- Active loans count
- Outstanding loan balance
- Recent member registrations
- Recent loan applications

#### Member Dashboard Shows:
- Total savings balance
- Active loans count
- Loan balance
- Personal accounts
- Active loans details

---

## Member Management

### Registering a New Member

1. **Access Registration Form**
   - Navigate to "Members" → "Register New Member"
   - Or click "+ Register New Member" button

2. **Fill Personal Information**
   - First Name (Required)
   - Last Name (Required)
   - ID/Passport Number (Required, Unique)
   - Phone Number (Required, Format: +254XXXXXXXXX)
   - Date of Birth (Optional)
   - Gender (Optional)
   - Address (Optional)
   - Department (Optional)

3. **Create Login Credentials**
   - Username (Required, Unique)
   - Email (Required, Valid email format)
   - Password (Required, Minimum 8 characters)

4. **Submit Registration**
   - Click "Register Member"
   - System automatically:
     - Creates user account
     - Generates unique member number (Format: LCWYYYY####)
     - Creates default savings account
     - Sends confirmation

### Viewing Members

1. Navigate to "Members"
2. View complete member list with:
   - Member number
   - Full name
   - ID number
   - Contact information
   - Total balance
   - Membership status
   - Join date

### Searching Members

**Search Options:**
- By name
- By member number
- By ID number

**Filter Options:**
- All status
- Active members
- Suspended members
- Inactive members

### Managing Member Status

**Suspend Member:**
1. Find member in list
2. Click "Edit"
3. Change status to "Suspended"
4. Provide reason
5. Save changes

**Activate Member:**
1. Find suspended member
2. Click "Edit"
3. Change status to "Active"
4. Save changes

---

## Account Management

### Account Types

1. **Savings Account**
   - Primary account for deposits
   - Earns interest
   - Accessible for withdrawals

2. **Shares Account**
   - Investment account
   - Higher interest rates
   - Restricted withdrawals

3. **Deposits Account**
   - Fixed deposits
   - Fixed term
   - Penalty for early withdrawal

### Viewing Accounts

**Admin View:**
1. Navigate to "Accounts"
2. View all member accounts
3. Filter by:
   - Account type
   - Member
   - Status

**Member View:**
1. Navigate to "My Accounts"
2. View personal accounts only
3. See:
   - Account number
   - Account type
   - Current balance
   - Account status

---

## Loan Management

### Loan Application Process (Member)

1. **Access Loan Application**
   - Navigate to "Apply for Loan"
   - Or Dashboard → "Apply for Loan" button

2. **Fill Application Form**
   - **Loan Type** (Required):
     - Emergency Loan
     - Development Loan
     - School Fees Loan
     - Business Loan
     - Personal Loan
   
   - **Amount Requested** (Required)
     - Minimum: KES 1,000
     - Maximum: As per settings
   
   - **Repayment Period** (Required):
     - 3, 6, 12, 18, 24, or 36 months
   
   - **Loan Purpose** (Required)
     - Detailed explanation
   
   - **Guarantors** (Optional):
     - Select up to 2 guarantors
     - Must be active members
   
   - **Collateral** (Optional):
     - Describe any collateral

3. **Submit Application**
   - Review details
   - Click "Submit Application"
   - Wait for approval notification

### Loan Approval Process (Admin/Committee)

1. **View Pending Applications**
   - Navigate to "Loans"
   - Filter by "Pending" status

2. **Review Application**
   - View member details
   - Check loan amount
   - Review purpose
   - Verify guarantors
   - Check member's savings

3. **Make Decision**
   
   **To Approve:**
   - Click "Approve" button
   - Add approval comments
   - System automatically:
     - Creates loan record
     - Generates loan number
     - Calculates installments
     - Sets maturity date
     - Updates status

   **To Reject:**
   - Click "Reject" button
   - Provide rejection reason
   - Submit

### Loan Repayment

**Admin Processing:**
1. Navigate to loan details
2. Click "Record Payment"
3. Enter:
   - Payment amount
   - Payment method (Cash, Bank Transfer, Mobile Money, Salary Deduction)
   - Payment date
4. System automatically:
   - Splits payment (interest/principal)
   - Updates loan balance
   - Generates receipt
   - Updates loan status if fully paid

### Viewing Loans

**Admin View:**
- All loans with filters:
  - Pending applications
  - Approved loans
  - Rejected applications
  - Active loans
  - Fully paid loans

**Member View:**
- Personal loans only
- Application status
- Repayment schedule
- Payment history

---

## Transaction Processing

### Processing Deposits

1. **Access Transaction Form**
   - Navigate to "Transactions"
   - Fill transaction form

2. **Enter Details**
   - Account ID (Required)
   - Amount (Required, Minimum: KES 1)
   - Description (Required)
   - Transaction type: "Deposit"

3. **Process**
   - Click "Process"
   - System automatically:
     - Updates account balance
     - Generates reference number
     - Creates receipt
     - Logs transaction

### Processing Withdrawals

1. **Enter Details**
   - Account ID
   - Amount
   - Description
   - Transaction type: "Withdrawal"

2. **Validation**
   - System checks sufficient balance
   - Processes if balance available
   - Shows error if insufficient

3. **Receipt**
   - Transaction reference generated
   - Balance updated
   - Audit trail created

### Transaction History

**View Options:**
- All transactions
- Filter by:
  - Transaction type (Deposit, Withdrawal)
  - Date range
  - Member
  - Account

**Details Shown:**
- Reference number
- Member name
- Account number
- Transaction type
- Amount
- Date and time
- Processed by
- Status

---

## Reports and Analytics

### Available Reports

1. **Member Reports**
   - Total members summary
   - Active members list
   - New registrations
   - Member statements

2. **Financial Reports**
   - Total savings
   - Account balances
   - Income statement
   - Balance sheet

3. **Loan Reports**
   - Loan portfolio
   - Active loans
   - Repayment schedule
   - Defaulters list
   - Loan performance

4. **Transaction Reports**
   - Daily transactions
   - Monthly summaries
   - Transaction by type
   - Audit trails

### Generating Reports

1. Navigate to "Reports"
2. Select report type
3. Set parameters:
   - Date range
   - Member (if applicable)
   - Account type (if applicable)
4. Click "Generate"
5. View online or download as PDF/CSV

---

## User Profile

### Viewing Profile

1. Navigate to "Profile"
2. View personal information
3. See account details

### Updating Profile

**Members Can Update:**
- Phone number
- Email
- Address
- City
- Password

**Cannot Update:**
- Name
- ID number
- Member number
- Login username

### Changing Password

1. Navigate to "Profile"
2. Click "Change Password"
3. Enter:
   - Current password
   - New password (minimum 8 characters)
   - Confirm new password
4. Click "Update Password"

---

## Best Practices

### Security
- ✓ Change default passwords immediately
- ✓ Use strong passwords (mix of letters, numbers, symbols)
- ✓ Log out after each session
- ✓ Don't share login credentials
- ✓ Report suspicious activities

### Data Entry
- ✓ Double-check all entries
- ✓ Use proper formats (phone numbers, emails)
- ✓ Verify member details before submission
- ✓ Keep descriptions clear and detailed
- ✓ Save work frequently

### Transaction Processing
- ✓ Verify account numbers
- ✓ Confirm amounts before processing
- ✓ Print receipts for record-keeping
- ✓ Balance accounts regularly
- ✓ Reconcile daily transactions

### Loan Management
- ✓ Review applications thoroughly
- ✓ Verify guarantor details
- ✓ Check member savings history
- ✓ Document approval reasons
- ✓ Track repayment schedules

---

## Troubleshooting

### Cannot Login
**Solutions:**
- Verify username/password
- Check CAPS LOCK is off
- Clear browser cache
- Contact administrator

### Cannot See Records
**Solutions:**
- Check your role permissions
- Verify filters are not too restrictive
- Refresh page
- Contact administrator

### Transaction Failed
**Solutions:**
- Verify account has sufficient balance (withdrawals)
- Check account status is "Active"
- Verify amount is positive
- Try again or contact administrator

### Cannot Submit Form
**Solutions:**
- Check all required fields are filled
- Verify formats (email, phone)
- Check for duplicate entries (ID number, username)
- Review error messages

---

## Keyboard Shortcuts

- `Ctrl + S` - Save form (where applicable)
- `Esc` - Close modal dialogs
- `Tab` - Navigate between fields
- `Enter` - Submit forms (when in last field)

---

## Getting Help

### Contact Support
- **Email**: admin@lacowe.jkuat.ac.ke
- **Phone**: +254 XXX XXX XXX
- **Office Hours**: Monday-Friday, 8AM-5PM

### Documentation
- User Guide (this document)
- Installation Guide (INSTALL.md)
- System Documentation (README.md)

---

## System Information

**Version**: 1.0.0  
**Release Date**: 2026  
**Developer**: Esther Wambui (SCT121-0518/2024)  
**Institution**: JKUAT - Department of Information Technology

---

**© 2026 LACOWE Welfare Group - JKUAT**
