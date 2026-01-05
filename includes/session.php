<?php
// includes/session.php
session_start();

function checkLogin($requiredRole = null) {
    // 1. Check if user is logged in
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
        header("Location: ../../login.php"); // Adjust path as needed based on file location
        exit;
    }

    // 2. Check if user has the correct role (if specific role required)
    if ($requiredRole !== null && $_SESSION['role'] !== $requiredRole) {
        // Redirect to their appropriate dashboard if they try to access wrong pages
        if ($_SESSION['role'] === 'admin') {
            header("Location: ../../modules/dashboard/dashboard.php");
        } elseif ($_SESSION['role'] === 'faculty') {
            header("Location: ../../faculty/dashboard.php");
        } elseif ($_SESSION['role'] === 'student') {
            header("Location: ../../student/dashboard.php");
        }
        exit;
    }
}
?>