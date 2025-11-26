<?php
require_once 'config.php';

if (isLoggedIn()) {
    logActivity($conn, $_SESSION['user_id'], 'User logged out');
}

session_destroy();
redirect('login.php');
?>