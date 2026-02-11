<?php

require_once __DIR__ . '/../vendor/autoload.php';
include "../Core/includes/Autoloader.php";
\Core\includes\Autoloader::register();

use Core\Routing\Router;
use Core\Utilis\SessionService;

// Start the session
SessionService::start();

// Load route definitions
require_once __DIR__ . '/../App/config/routes.php';

// Get request path and method
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: "/";
$method = $_SERVER['REQUEST_METHOD'];

// Create router and dispatch
new Router($path, $method);