<?php
ob_start();

/**
 * Vercel Serverless Entry Point (Router)
 * This script bridges the Vercel /api requirement to the root project.
 */

// 1. Change working directory to project root so all 'require' paths work correctly
chdir(__DIR__ . '/..');

// 2. Get the requested URL path
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// 3. Handle the root path
if ($uri === '/' || $uri === '' || $uri === '/index.php') {
    require_once 'index.php';
    exit;
}

// 4. Check if the requested file exists in the root (e.g., /login.php)
$requestedFile = ltrim($uri, '/');

// Security check: only allow PHP files from the root directory
if (preg_match('/\.php$/', $requestedFile) && file_exists($requestedFile)) {
    require_once $requestedFile;
}
else {
    // If no file matches, fallback to index.php or show 404
    http_response_code(404);
    echo "404 - File Not Found: " . htmlspecialchars($uri);
}

// Ensure session is saved before the script ends (important for Vercel/proxied environments)
if (session_status() === PHP_SESSION_ACTIVE) {
    session_write_close();
}
