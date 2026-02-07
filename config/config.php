<?php
/**
 * Database Configuration
 * LACOWE Welfare MIS
 */

// Database Configuration
define('DB_DRIVER', 'mysql'); // 'mysql' (Local) or 'pgsql' (Supabase/Production)
define('DB_HOST', 'localhost');
define('DB_NAME', 'lacowe_welfare_mis');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_PORT', '5432'); // Default for PostgreSQL

// Application Configuration
define('APP_NAME', 'LACOWE Welfare MIS');
define('APP_URL', 'http://localhost/lacowe-welfare-mis');
define('APP_VERSION', '1.0.0');

// Security Configuration
define('SESSION_LIFETIME', 3600); // 1 hour in seconds
define('PASSWORD_MIN_LENGTH', 8);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_TIME', 900); // 15 minutes in seconds

// File Upload Configuration
define('UPLOAD_MAX_SIZE', 5242880); // 5MB in bytes
define('ALLOWED_FILE_TYPES', ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png']);

// Pagination
define('RECORDS_PER_PAGE', 20);

// Date Format
define('DATE_FORMAT', 'Y-m-d');
define('DATETIME_FORMAT', 'Y-m-d H:i:s');
define('DISPLAY_DATE_FORMAT', 'd-m-Y');
define('DISPLAY_DATETIME_FORMAT', 'd-m-Y H:i:s');

// Currency
define('CURRENCY_SYMBOL', 'KES');
define('CURRENCY_DECIMAL_PLACES', 2);

// Error Reporting (Change to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('Africa/Nairobi');

// Session Configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS

?>
