<?php
/**
 * Database Configuration
 * MySQL Remote Database Connection
 */

// Database credentials
define('DB_HOST', 'auth-db1274.hstgr.io');
define('DB_PORT', 3306);
define('DB_USER', 'u334425891_1930');
define('DB_PASSWORD', '1tRK>$My');
define('DB_NAME', 'u334425891_1930');

// Create database connection
function getDBConnection() {
    static $conn = null;
    
    if ($conn === null) {
        try {
            $conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, DB_PORT);
            
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }
            
            // Set charset to utf8mb4 for proper Unicode support
            $conn->set_charset("utf8mb4");
            
        } catch (Exception $e) {
            die("Database connection error: " . $e->getMessage());
        }
    }
    
    return $conn;
}

// Close database connection
function closeDBConnection() {
    global $conn;
    if (isset($conn) && $conn !== null) {
        $conn->close();
    }
}

