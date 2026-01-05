<?php
/**
 * Database Configuration
 * MySQL Remote Database Connection
 */

// Database credentials - Use environment variables for production hosting
define('DB_HOST', getenv('DB_HOST') ?: 'auth-db1274.hstgr.io');
define('DB_PORT', getenv('DB_PORT') ?: 3306);
define('DB_USER', getenv('DB_USER') ?: 'u334425891_1930');
define('DB_PASSWORD', getenv('DB_PASSWORD') ?: '1tRK>$My');
define('DB_NAME', getenv('DB_NAME') ?: 'u334425891_1930');

// Create database connection
function getDBConnection() {
    static $conn = null;
    
    if ($conn === null) {
        try {
            $conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, DB_PORT);
            
            if ($conn->connect_error) {
                // Log error instead of dying to prevent 500 errors
                error_log("Database connection failed: " . $conn->connect_error);
                // Return false instead of null to maintain compatibility
                return false;
            }
            
            // Set charset to utf8mb4 for proper Unicode support
            $conn->set_charset("utf8mb4");
            
        } catch (Exception $e) {
            error_log("Database connection error: " . $e->getMessage());
            return false;
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

