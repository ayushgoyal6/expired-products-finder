<?php
// Logout Script
// Destroys session and redirects to login

require_once 'config.php';

// Destroy all session data
session_destroy();

// Redirect to login page
redirect('login.php');

?>
