<?php
require_once '../includes/functions.php';

// Destroy all session data
session_start();
session_destroy();

// Redirect to homepage
header('Location: /ecommerce-platform/index.php');
exit;
?>