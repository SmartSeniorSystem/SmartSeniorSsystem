<?php
require_once 'config.php';

// If user is not logged in, kick them out to login page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Function to check if the user has the correct role for the page
function checkRole($requiredRole) {
    if ($_SESSION['role'] !== $requiredRole) {
        header("Location: login.php?error=unauthorized");
        exit();
    }
}
?>