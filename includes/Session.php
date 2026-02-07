<?php
/**
 * Session Management Class
 * Handles all session operations
 * LACOWE Welfare MIS
 */

class Session
{

    /**
     * Start session if not already started
     */
    public static function start()
    {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            // Set session cookie parameters for security
            session_set_cookie_params([
                'lifetime' => 0,
                'path' => '/',
                'domain' => '',
                'secure' => false, // Set to true if using HTTPS
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
            session_start();
        }
    }

    /**
     * Set session variable
     */
    public static function set($key, $value)
    {
        self::start();
        $_SESSION[$key] = $value;
    }

    /**
     * Get session variable
     */
    public static function get($key, $default = null)
    {
        self::start();
        return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
    }

    /**
     * Check if session variable exists
     */
    public static function has($key)
    {
        self::start();
        return isset($_SESSION[$key]);
    }

    /**
     * Remove session variable
     */
    public static function remove($key)
    {
        self::start();
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    /**
     * Destroy session
     */
    public static function destroy()
    {
        self::start();
        session_unset();
        session_destroy();
    }

    /**
     * Set flash message
     */
    public static function flash($key, $message, $type = 'info')
    {
        self::set('flash_' . $key, [
            'message' => $message,
            'type' => $type
        ]);
    }

    /**
     * Get and remove flash message
     */
    public static function getFlash($key)
    {
        $flash = self::get('flash_' . $key);
        self::remove('flash_' . $key);
        return $flash;
    }

    /**
     * Check if user is logged in
     */
    public static function isLoggedIn()
    {
        return self::has('user_id') && self::has('role_id');
    }

    /**
     * Get logged in user ID
     */
    public static function getUserId()
    {
        return self::get('user_id');
    }

    /**
     * Get logged in user role
     */
    public static function getUserRole()
    {
        return self::get('role_id');
    }

    /**
     * Get logged in user name
     */
    public static function getUserName()
    {
        return self::get('username');
    }

    /**
     * Set user session data
     */
    public static function setUser($userData)
    {
        self::set('user_id', $userData['user_id']);
        self::set('username', $userData['username']);
        self::set('email', $userData['email']);
        self::set('role_id', $userData['role_id']);
        self::set('role_name', $userData['role_name']);
        self::set('member_id', $userData['member_id'] ?? null);
    }

    /**
     * Clear user session data
     */
    public static function clearUser()
    {
        self::remove('user_id');
        self::remove('username');
        self::remove('email');
        self::remove('role_id');
        self::remove('role_name');
        self::remove('member_id');
    }

    /**
     * Regenerate session ID for security
     */
    public static function regenerate()
    {
        self::start();
        session_regenerate_id(true);
    }

    /**
     * Get CSRF token
     */
    public static function getCsrfToken()
    {
        if (!self::has('csrf_token')) {
            self::set('csrf_token', bin2hex(random_bytes(32)));
        }
        return self::get('csrf_token');
    }

    /**
     * Verify CSRF token
     */
    public static function verifyCsrfToken($token)
    {
        return hash_equals(self::getCsrfToken(), $token);
    }
}
?>
