<?php
/**
 * Session Management
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['username']);
}

// Require login - redirect to login page if not authenticated
function requireLogin() {
    if (!isLoggedIn()) {
        // Get the current directory path
        $currentPath = dirname($_SERVER['PHP_SELF']);
        if ($currentPath === '/') $currentPath = '';
        header('Location: ' . $currentPath . '/login.php');
        exit();
    }
}

// Login user
function loginUser($userId, $username) {
    $_SESSION['user_id'] = $userId;
    $_SESSION['username'] = $username;
    $_SESSION['login_time'] = time();
}

// Logout user
function logoutUser() {
    session_unset();
    session_destroy();
    // Get the current directory path
    $currentPath = dirname($_SERVER['PHP_SELF']);
    if ($currentPath === '/') $currentPath = '';
    header('Location: ' . $currentPath . '/login.php');
    exit();
}

// Get current user ID
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

// Get current username
function getCurrentUsername() {
    return $_SESSION['username'] ?? null;
}

