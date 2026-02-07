<?php
/**
 * Index Page - Redirects to login or dashboard
 * LACOWE Welfare MIS
 */

require_once 'config/config.php';


if (Session::isLoggedIn()) {
    redirect('dashboard.php');
}
else {
    redirect('login.php');
}
?>
