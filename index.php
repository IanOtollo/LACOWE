<?php
/**
 * Index Page - Redirects to login or dashboard
 * LACOWE Welfare MIS
 */

require_once 'config/config.php';
require_once 'includes/Session.php';

Session::start();

if (Session::isLoggedIn()) {
    header('Location: dashboard.php');
} else {
    header('Location: login.php');
}
exit();
?>
