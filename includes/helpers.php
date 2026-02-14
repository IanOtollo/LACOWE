<?php
/**
 * Helper Functions
 * Common utility functions used throughout the application
 * LACOWE Welfare MIS
 */

/**
 * Sanitize input data
 */
function sanitize($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Validate email
 */
function isValidEmail($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate phone number (Kenyan format)
 */
function isValidPhone($phone)
{
    $pattern = '/^(\+254|0)[17]\d{8}$/';
    return preg_match($pattern, $phone);
}

/**
 * Format currency
 */
function formatCurrency($amount)
{
    return CURRENCY_SYMBOL . ' ' . number_format($amount, CURRENCY_DECIMAL_PLACES);
}

/**
 * Format date
 */
function formatDate($date, $format = DISPLAY_DATE_FORMAT)
{
    if (!$date)
        return '';
    return date($format, strtotime($date));
}

/**
 * Format datetime
 */
function formatDateTime($datetime, $format = DISPLAY_DATETIME_FORMAT)
{
    if (!$datetime)
        return '';
    return date($format, strtotime($datetime));
}

/**
 * Generate unique reference number
 */
function generateReference($prefix = 'REF')
{
    return $prefix . '-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
}

/**
 * Calculate age from date of birth
 */
function calculateAge($dob)
{
    $birthDate = new DateTime($dob);
    $today = new DateTime('today');
    $age = $birthDate->diff($today)->y;
    return $age;
}

/**
 * Get time ago format
 */
function timeAgo($datetime)
{
    $timestamp = strtotime($datetime);
    $difference = time() - $timestamp;

    $periods = [
        ['year', 31536000],
        ['month', 2592000],
        ['week', 604800],
        ['day', 86400],
        ['hour', 3600],
        ['minute', 60],
        ['second', 1]
    ];

    foreach ($periods as $key => $value) {
        $count = floor($difference / $value[1]);
        if ($count > 0) {
            return $count . ' ' . $value[0] . ($count > 1 ? 's' : '') . ' ago';
        }
    }

    return 'just now';
}

/**
 * Redirect to URL
 */
function redirect($page)
{
    // If it's already a full URL, just go there
    if (filter_var($page, FILTER_VALIDATE_URL)) {
        header("Location: $page");
        exit();
    }

    // Otherwise, ensure we have a clean path
    $page = ltrim($page, '/');

    // In Vercel/proxied environments, relative headers can be flaky.
    // Try to build an absolute-compliant path if possible, or just use the local path.
    session_write_close();
    header("Location: $page");
    exit();
}

/**
 * Show alert message
 */
function showAlert($type, $message)
{
    $icons = [
        'success' => '✓',
        'danger' => '✕',
        'warning' => '⚠',
        'info' => 'ℹ'
    ];

    $icon = $icons[$type] ?? 'ℹ';

    return "<div class='alert alert-{$type} alert-dismissible fade show' role='alert'>
                <span class='alert-icon'>{$icon}</span>
                <span class='alert-message'>{$message}</span>
                <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
            </div>";
}

/**
 * Validate required fields
 */
function validateRequired($fields, $data)
{
    $errors = [];
    foreach ($fields as $field) {
        if (!isset($data[$field]) || empty(trim($data[$field]))) {
            $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
        }
    }
    return $errors;
}

/**
 * Generate password
 */
function generatePassword($length = 10)
{
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
    $password = '';
    $charLength = strlen($chars) - 1;

    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[rand(0, $charLength)];
    }

    return $password;
}

/**
 * Paginate results
 */
function paginate($currentPage, $totalRecords, $recordsPerPage = RECORDS_PER_PAGE)
{
    $totalPages = ceil($totalRecords / $recordsPerPage);
    $currentPage = max(1, min($currentPage, $totalPages));
    $offset = ($currentPage - 1) * $recordsPerPage;

    return [
        'current_page' => $currentPage,
        'total_pages' => $totalPages,
        'total_records' => $totalRecords,
        'records_per_page' => $recordsPerPage,
        'offset' => $offset,
        'has_previous' => $currentPage > 1,
        'has_next' => $currentPage < $totalPages
    ];
}

/**
 * Generate pagination HTML
 */
function paginationHTML($pagination, $url)
{
    if ($pagination['total_pages'] <= 1)
        return '';

    $html = '<nav aria-label="Page navigation"><ul class="pagination justify-content-center">';

    // Previous button
    if ($pagination['has_previous']) {
        $prevPage = $pagination['current_page'] - 1;
        $html .= "<li class='page-item'><a class='page-link' href='{$url}?page={$prevPage}'>Previous</a></li>";
    }
    else {
        $html .= "<li class='page-item disabled'><span class='page-link'>Previous</span></li>";
    }

    // Page numbers
    $start = max(1, $pagination['current_page'] - 2);
    $end = min($pagination['total_pages'], $pagination['current_page'] + 2);

    if ($start > 1) {
        $html .= "<li class='page-item'><a class='page-link' href='{$url}?page=1'>1</a></li>";
        if ($start > 2) {
            $html .= "<li class='page-item disabled'><span class='page-link'>...</span></li>";
        }
    }

    for ($i = $start; $i <= $end; $i++) {
        $active = $i == $pagination['current_page'] ? 'active' : '';
        $html .= "<li class='page-item {$active}'><a class='page-link' href='{$url}?page={$i}'>{$i}</a></li>";
    }

    if ($end < $pagination['total_pages']) {
        if ($end < $pagination['total_pages'] - 1) {
            $html .= "<li class='page-item disabled'><span class='page-link'>...</span></li>";
        }
        $html .= "<li class='page-item'><a class='page-link' href='{$url}?page={$pagination['total_pages']}'>{$pagination['total_pages']}</a></li>";
    }

    // Next button
    if ($pagination['has_next']) {
        $nextPage = $pagination['current_page'] + 1;
        $html .= "<li class='page-item'><a class='page-link' href='{$url}?page={$nextPage}'>Next</a></li>";
    }
    else {
        $html .= "<li class='page-item disabled'><span class='page-link'>Next</span></li>";
    }

    $html .= '</ul></nav>';

    return $html;
}

/**
 * Upload file
 */
function uploadFile($file, $destination, $allowedTypes = ALLOWED_FILE_TYPES)
{
    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        return ['success' => false, 'message' => 'No file uploaded'];
    }

    // Check file size
    if ($file['size'] > UPLOAD_MAX_SIZE) {
        return ['success' => false, 'message' => 'File size exceeds maximum allowed size'];
    }

    // Check file type
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $allowedTypes)) {
        return ['success' => false, 'message' => 'File type not allowed'];
    }

    // Generate unique filename
    $filename = uniqid() . '_' . basename($file['name']);
    $filepath = $destination . '/' . $filename;

    // Move file
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => true, 'filename' => $filename, 'filepath' => $filepath];
    }
    else {
        return ['success' => false, 'message' => 'Failed to upload file'];
    }
}

/**
 * Export to CSV
 */
function exportToCSV($data, $filename, $headers = null)
{
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $output = fopen('php://output', 'w');

    if ($headers && !empty($data)) {
        fputcsv($output, $headers);
    }

    foreach ($data as $row) {
        fputcsv($output, $row);
    }

    fclose($output);
    exit();
}

/**
 * Calculate loan installment
 */
function calculateLoanInstallment($principal, $annualRate, $months)
{
    $monthlyRate = ($annualRate / 100) / 12;
    if ($monthlyRate == 0) {
        return $principal / $months;
    }

    $installment = $principal * ($monthlyRate * pow(1 + $monthlyRate, $months)) / (pow(1 + $monthlyRate, $months) - 1);
    return round($installment, 2);
}

/**
 * Calculate total loan amount
 */
function calculateTotalLoanAmount($principal, $annualRate, $months)
{
    $installment = calculateLoanInstallment($principal, $annualRate, $months);
    return round($installment * $months, 2);
}

/**
 * Get status badge HTML
 */
function getStatusBadge($status)
{
    $badges = [
        'Active' => 'success',
        'Pending' => 'warning',
        'Approved' => 'success',
        'Rejected' => 'danger',
        'Completed' => 'success',
        'Failed' => 'danger',
        'Suspended' => 'danger',
        'Inactive' => 'secondary',
        'Defaulted' => 'danger',
        'Fully Paid' => 'success'
    ];

    $class = $badges[$status] ?? 'secondary';
    return "<span class='badge bg-{$class}'>{$status}</span>";
}

/**
 * Debug function (only in development)
 */
function dd($data)
{
    echo '<pre>';
    var_dump($data);
    echo '</pre>';
    die();
}

/**
 * Log error
 */
function logError($message, $context = [])
{
    $logMessage = date('Y-m-d H:i:s') . ' - ' . $message;
    if (!empty($context)) {
        $logMessage .= ' - Context: ' . json_encode($context);
    }
    error_log($logMessage);
}

?>
