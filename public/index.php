<?php

require_once __DIR__ . '/../vendor/autoload.php';
include "../Core/Autoloader.php";
\Core\Autoloader::register();

use App\GUI\Controllers\SAE\PageSaeController;
use App\GUI\Controllers\Dashboard\DashboardController;
use App\GUI\Controllers\Index\IndexController;
use App\GUI\Controllers\Info\LegalNoticeController;
use App\GUI\Controllers\Info\SiteMapController;
use App\GUI\Controllers\pwd\ForgotPasswordController;
use App\GUI\Controllers\pwd\ForgotPasswordPostController;
use App\GUI\Controllers\pwd\ResetPasswordController;
use App\GUI\Controllers\pwd\ResetPasswordPostController;
use App\GUI\Controllers\SAE\CreateSaeController;
use App\GUI\Controllers\SAE\CreateSaePostController;
use App\GUI\Controllers\SAE\ManageGroupsController;
use App\GUI\Controllers\SAE\ManageGroupsPostController;
use App\GUI\Controllers\Settings\DeleteUserController;
use App\GUI\Controllers\Settings\EditProfileController;
use App\GUI\Controllers\Settings\EditProfilePost;
use App\GUI\Controllers\Settings\SettingsController;
use App\GUI\Controllers\ToDoList\ToDoListController;
use App\GUI\Controllers\ToDoList\ToDoListPost;
use App\GUI\Controllers\User\Login;
use App\GUI\Controllers\User\LoginPost;
use App\GUI\Controllers\User\Logout;
use App\GUI\Controllers\User\Register;
use App\GUI\Controllers\User\RegisterPost;
use App\Infrastructure\Service\SessionService;

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
    new EditProfilePost(),
    new ManageGroupsController(),
    new ManageGroupsPostController()
];

// Start the session with a cookie params.
SessionService::start();

// Automatic routing.
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: "";
foreach ($controllers as $controller) {
    if ($controller::support($path, $_SERVER['REQUEST_METHOD'])) {
        try {
            $controller->control();
            exit();
        }
        catch (\Throwable $e) {
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