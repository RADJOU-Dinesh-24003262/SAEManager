<?php
session_start();
include "_assets/includes/Autoloader.php";

use Controllers\User\Login;
use Controllers\User\LoginPost;
use Controllers\User\Register;
//use Controllers\User\LoginPost;
use Controllers\User\RegisterPost;
//use Controllers\Dashboard\Home;
use Controllers\AssetController;    
use Controllers\Index\IndexController;
use Controllers\Info\LegalNoticeController;
use Controllers\Info\SiteMapController;
use Controllers\pwd\ForgotPasswordController;
use Controllers\pwd\ForgotPasswordPostController;
use Controllers\pwd\ResetPasswordController;
use Controllers\pwd\ResetPasswordPostController;

//phpinfo();

// List of available controllers
$controllers = [
    new Login(),
    new Register(), 
    new LoginPost(),
    new RegisterPost(),
    //new Home(),
    new LegalNoticeController(),
    new SiteMapController(),
    new IndexController(),

    new ForgotPasswordController(),
    new ForgotPasswordPostController(),
    new ResetPasswordController(),
    new ResetPasswordPostController()
];

// automatic routing
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
foreach ($controllers as $controller) {
    if ($controller::support($path, $_SERVER['REQUEST_METHOD'])) {
        try {
            $controller->control();
            exit();
        } catch (Exception $e) {
            // Log error and show error page
            error_log("Erreur contrôleur: " . $e->getMessage());
            http_response_code(500);
            echo "Erreur interne du serveur";
            exit();
        }
    }
}

// 404 - Route not found
http_response_code(404);
echo "Page non trouvée";
exit();