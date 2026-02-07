<?php
require_once 'config/config.php';
require_once 'includes/Auth.php';
require_once 'includes/Session.php';

$auth = new Auth();
$auth->logout();

header('Location: login.php');
exit();
?>
