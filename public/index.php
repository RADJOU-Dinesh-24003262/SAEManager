<?php

require_once __DIR__ . '/../vendor/autoload.php';
include "../Core/Includes/Autoloader.php";
\Core\Includes\Autoloader::register();

use Core\Routing\Router;
use Core\Utils\SessionService;

// Start the session
SessionService::start();

// Get request path and method
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: "/";
$method = $_SERVER['REQUEST_METHOD'];

// Create router and dispatch
new Router($path, $method);
