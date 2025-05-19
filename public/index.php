<?php
// public/index.php

// AT THE VERY TOP:
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ob_start(); // Start output buffering

// Define a base path if your application is in a subdirectory.
define('BASE_PATH', '/HopOn'); // Adjust if your app is not directly at the web root

// Require Composer's autoloader FIRST
$autoloaderPath = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($autoloaderPath)) {
    ob_end_flush(); // Make sure to flush if we die here
    die('Composer autoloader not found. Please run "composer install" in the project root (HopOn/).');
}
require_once $autoloaderPath;

$router = new Core\Router();
$router->handleRequest();

ob_end_flush(); // Flush the buffer at the very end
?>