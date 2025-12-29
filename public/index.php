<?php

require_once __DIR__ . '/../vendor/autoload.php';
include "../Core/includes/Autoloader.php";
\Core\includes\Autoloader::register();

use App\Controllers\SAE\PageSaeController;
use Controllers\Dashboard\DashboardController;
use Controllers\Index\IndexController;
use Controllers\Info\LegalNoticeController;
use Controllers\Info\SiteMapController;
use Controllers\pwd\ForgotPasswordController;
use Controllers\pwd\ForgotPasswordPostController;
use Controllers\pwd\ResetPasswordController;
use Controllers\pwd\ResetPasswordPostController;
use Controllers\SAE\CreateSaeController;
use Controllers\SAE\CreateSaePostController;
use Controllers\Settings\DeleteUserController;
use Controllers\Settings\EditProfileController;
use Controllers\Settings\EditProfilePost;
use Controllers\Settings\SettingsController;
use Controllers\ToDoList\ToDoListController;
use Controllers\ToDoList\ToDoListPost;
use Controllers\User\Login;
use Controllers\User\LoginPost;
use Controllers\User\Logout;
use Controllers\User\Register;
use Controllers\User\RegisterPost;
use Core\Utilis\SessionService;

// List of available controllers.
$controllers = [
    new Login(),
    new Register(),
    new LoginPost(),
    new RegisterPost(),
    new LegalNoticeController(),
    new SiteMapController(),
    new IndexController(),
    new Logout(),
    new ForgotPasswordController(),
    new ForgotPasswordPostController(),
    new ResetPasswordController(),
    new ResetPasswordPostController(),
    new PageSaeController(),
    new ToDoListController(),
    new CreateSaeController(),
    new CreateSaePostController(),
    new DashboardController(),
    new SettingsController(),
    new DeleteUserController(),
    new ToDoListPost(),
    new EditProfileController(),
    new EditProfilePost()
];

// start the session with a cookie params
SessionService::start();

// Automatic routing.
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: "";
foreach ($controllers as $controller) {
    if ($controller::support($path, $_SERVER['REQUEST_METHOD'])) {
        try {
            $controller->control();
            exit();
        } catch (\Throwable $e) {
            // Generical fallback for unexpected errors.
            /* SessionService::destroy(); */
            SessionService::setFlash('errors', ["Une erreur inattendue est survenue."]);
            error_log("Erreur inattendue: " . $e->getTraceAsString() . $e->getMessage());
            http_response_code(500);
            header("Location: /");
            exit();
        }
    }
}

// 404 - Route not found
http_response_code(404);
SessionService::setFlash('errors', "Page non existante.");
header("Location: /");
exit();
