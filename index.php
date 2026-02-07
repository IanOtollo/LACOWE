<?php
/**
 * Index Page - Redirects to login or dashboard
 * LACOWE Welfare MIS
 */

require_once 'config/config.php';


if (Session::isLoggedIn()) {
    header('Location: dashboard.php');
}
else {
    header('Location: login.php');
}
exit();
?>
