<?php
require_once '../includes/functions.php';

// Destroy session
session_start();
session_destroy();

// Redirect to login page
redirect('/kelulusan2025/admin/login.php');
?>
