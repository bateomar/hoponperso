<?php
// config/db_connection.php

$autoloaderPath = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($autoloaderPath)) {
    // Log this critical error, but don't echo detailed paths to the user in production.
    error_log("FATAL: Composer autoloader not found at {$autoloaderPath}.");
    die("A critical error occurred. Please contact support. (Error Code: CA01)");
}
require_once $autoloaderPath;

try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
} catch (\Throwable $e) {
    error_log("FATAL: Error loading .env file: " . $e->getMessage());
    die("A critical error occurred. Please contact support. (Error Code: ENV01)");
}

$db_host   = $_ENV['MYSQL_HOST'] ?? null;
$db_name = $_ENV['MYSQL_DATABASE'] ?? null;
$db_user = $_ENV['MYSQL_USER'] ?? null;
$db_pass = $_ENV['MYSQL_PASSWORD'] ?? null; // Password can be empty
$db_charset = $_ENV['MYSQL_CHARSET'] ?? 'utf8mb4';

// Critical check: Ensure essential variables are loaded.
if ($db_host === null || $db_name === null || $db_user === null) {
    error_log("FATAL: Database configuration (HOST, DATABASE, USER) missing or not loaded correctly from .env.");
    die("A critical error occurred with the application configuration. Please contact support. (Error Code: DBC01)");
}

$dsn = "mysql:host={$db_host};dbname={$db_name};charset={$db_charset}";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

$pdo = null;
try {
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);
} catch (PDOException $e) {
    error_log("FATAL: Database connection failed: " . $e->getMessage() . " DSN: " . $dsn);
    // Provide a generic error message to the user.
    die("Could not connect to the database. Please try again later or contact support. (Error Code: DBH01)");
}

// This check is good practice, though with ATTR_ERRMODE => ERRMODE_EXCEPTION,
// new PDO() would throw an exception rather than return null on failure.
if ($pdo === null) {
    error_log("FATAL: PDO object is null after connection attempt without an exception being caught. This is highly unexpected.");
    die("An unexpected error occurred with the database connection. Please contact support. (Error Code: DBH02)");
}

return $pdo;
?>