<?php
require_once 'functions.php';

// Check if user is logged in, if not redirect to login page
if (!isLoggedIn()) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    redirect('login.php');
}
?>