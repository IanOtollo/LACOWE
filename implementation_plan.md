# Implementation Plan - Professional Fullstack Refactoring

## User Review Required
> [!IMPORTANT]
> This refactor will change how the `dashboard.php` and `member-dashboard.php` interact with the database. It will replace raw SQL with Model method calls. This is a cleaner, more maintainable approach but requires testing to ensure no data is lost in the view.

## Proposed Changes

### Backend Architecture
#### [MODIFY] [dashboard.php](file:///c:/xampp/htdocs/lacowe-welfare-mis/dashboard.php)
- Remove raw SQL queries.
- Instantiate `Member`, `Loan`, and `Transaction` models.
- Use model methods (`count()`, `getStatistics()`, `getRecent()`) to fetch data.
- Keep the View logic (HTML) but feed it with cleaner data structures.

#### [MODIFY] [member-dashboard.php](file:///c:/xampp/htdocs/lacowe-welfare-mis/member-dashboard.php)
- Similar refactor: Remove raw SQL.
- Use `Member->getSummary($id)` and `Loan->getByMember($id)` methods.

#### [MODIFY] [includes/Database.php](file:///c:/xampp/htdocs/lacowe-welfare-mis/includes/Database.php)
- Ensure it uses Singleton pattern or efficient connection reusing to prevent "Too many connections" errors if we instantiate multiple models.

### Models
#### [MODIFY] [models/Member.php](file:///c:/xampp/htdocs/lacowe-welfare-mis/models/Member.php)
- Add missing methods if needed by the dashboard (e.g., `getRecentMembers()`).

#### [MODIFY] [models/Loan.php](file:///c:/xampp/htdocs/lacowe-welfare-mis/models/Loan.php)
- Ensure it has methods like `getStats()`, `getRecentApplications()`.

## Verification Plan

### Automated Tests
- None available currently in the project. We will rely on manual verification.

### Manual Verification
1.  **Admin Dashboard**:
    - Login as Admin.
    - Verify "Total Members", "Active Loans", and "Outstanding Balance" cards show numbers, not errors.
    - Verify "Recent Members" table lists data.
2.  **Member Dashboard**:
    - Login as Member.
    - Verify personal stats (Savings, Loan Balance) are correct.
