<?php
/**
 * Database Configuration
 * LACOWE Welfare MIS
 */

// Database Configuration
define('DB_DRIVER', getenv('DB_DRIVER') ?: 'mysql'); // 'mysql' (Local) or 'pgsql' (Supabase/Production)
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'lacowe_welfare_mis');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_PORT', getenv('DB_PORT') ?: '5432'); // Default for PostgreSQL

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
if (!defined('RECORDS_PER_PAGE'))
    define('RECORDS_PER_PAGE', 20);

// Date Format
if (!defined('DATE_FORMAT'))
    define('DATE_FORMAT', 'Y-m-d');
if (!defined('DATETIME_FORMAT'))
    define('DATETIME_FORMAT', 'Y-m-d H:i:s');
if (!defined('DISPLAY_DATE_FORMAT'))
    define('DISPLAY_DATE_FORMAT', 'd-m-Y');
if (!defined('DISPLAY_DATETIME_FORMAT'))
    define('DISPLAY_DATETIME_FORMAT', 'd-m-Y H:i:s');

// Currency
if (!defined('CURRENCY_SYMBOL'))
    define('CURRENCY_SYMBOL', 'KES');
if (!defined('CURRENCY_DECIMAL_PLACES'))
    define('CURRENCY_DECIMAL_PLACES', 2);

// Error Reporting (Change to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('Africa/Nairobi');

// Session Configuration
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
}
