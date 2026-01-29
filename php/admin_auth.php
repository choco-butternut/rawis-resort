<?php

if(session_status()==PHP_SESSION_NONE)session_start();

if(!isset($_SESSION["admin_logged_in"]) || $_SESSION["admin_logged_in"] !== true){
    header("Location: index.php");
    exit();
}

$timeout_duration = 1800; 

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: index.php?timeout=1");
    exit();
}

$_SESSION['last_activity'] = time();

$admin_username = $_SESSION['admin_username'];
$admin_role = $_SESSION['admin_role'];

?>