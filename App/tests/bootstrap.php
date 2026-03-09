<?php

// phpcs:disable
// App/tests/bootstrap.php

/**
 * PHPUnit Bootstrap File
 */

use Core\Utils\SessionService;

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Define application environment
putenv('APP_ENV=testing');
define('APP_ENV', 'testing');


// Load Composer autoloader
require_once __DIR__ . '/../../vendor/autoload.php';

// Load Special Autoloader for the tests
spl_autoload_register(function ($class) {
    $file = 'App' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR .
    str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
    $coreFile = str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';

    if (file_exists($file)) {
        require $file;
    } elseif (file_exists($coreFile)) {
        require $coreFile;
    }
});


// Start session for tests
if (session_status() === PHP_SESSION_NONE) {
    SessionService::start();
}

// Database configuration for tests (you can use SQLite for faster tests)
// Or mock the database class
