<?php

// phpcs:disable

/**
 * PHPUnit Bootstrap File
 */

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Define application environment
define('APP_ENV', 'testing');

// Load Composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../_assets/includes/Autoloader.php';

// Start session for tests
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database configuration for tests (you can use SQLite for faster tests)
// Or mock the database class
