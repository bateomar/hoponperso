<?php
// Database connection configuration - MySQL (HopOn external)
function connectDB() {
    // Store the PDO instance as a static variable - only created once
    static $pdo = null;
    
    // If we already have a connection, return it
    if ($pdo !== null) {
        return $pdo;
    }
    
    try {
        // MySQL connection details from your shared information
        $host = 'herogu.garageisep.com';
        $dbname = 'NvChx418Vk_hopon';
        $user = 'd3UG45BFAl_hopon';
        $password = 'MNYObOzVNqptcHLu';
        
        $dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            // Force a new connection to prevent "too many connections" error
            PDO::ATTR_PERSISTENT => false
        ];
        
        // Create a new PDO instance
        $pdo = new PDO($dsn, $user, $password, $options);
        return $pdo;
    } catch (PDOException $e) {
        // Log error details to server logs instead of displaying them
        error_log('Database Connection Error: ' . $e->getMessage());
        return false;
    }
}

// For MySQL external database, we assume the database structure already exists
function initializeDatabase() {
    $db = connectDB();
    if (!$db) {
        error_log('Failed to connect to database during initialization');
        return false;
    }
    
    try {
        // Just verify that we can access the tables
        $stmt = $db->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        error_log('Successfully connected to MySQL database. Tables found: ' . implode(', ', $tables));
        
        // Check if the required tables exist
        if (!in_array('users', $tables) || !in_array('trips', $tables)) {
            error_log('Warning: Required tables (users, trips) not found in the database.');
        }
        
        return true;
    } catch (PDOException $e) {
        error_log('Database Initialization Error: ' . $e->getMessage());
        return false;
    }
}

// Initialize the database when this file is included
initializeDatabase();
?>