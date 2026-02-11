<?php

// phpcs:disable
// App/tests/bootstrap.php

/**
 * PHPUnit Bootstrap File
 */

use App\Infrastructure\Service\SessionService;

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Define application environment
putenv('APP_ENV=testing');
define('APP_ENV', 'testing');


// Load Composer autoloader
require_once __DIR__ . '/../../vendor/autoload.php';

// Custom autoloader removed in favor of Composer


// Start session for tests
if (session_status() === PHP_SESSION_NONE) {
    SessionService::start();
}

// Database configuration for tests (you can use SQLite for faster tests)
// Or mock the database class