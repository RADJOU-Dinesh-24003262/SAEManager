<?php
session_start();
include "_assets/includes/Autoloader.php";

use Controllers\User\Login;
use Controllers\User\Register;
//use Controllers\User\LoginPost;
use Controllers\User\RegisterPost;
//use Controllers\Dashboard\Home;
use Controllers\AssetController;    
use Controllers\Menu\MenuController;
// Liste des contrôleurs disponibles
$controllers = [
    //new Login(),
    new Register(), 
    //new LoginPost(),
    new RegisterPost(),
    //new Home(),

    new MenuController(),
];

// Routing automatique
foreach ($controllers as $controller) {
    if ($controller::support($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD'])) {
        try {
            $controller->control();
            exit();
        } catch (Exception $e) {
            // Log de l'erreur et affichage d'une page d'erreur
            error_log("Erreur contrôleur: " . $e->getMessage());
            http_response_code(500);
            echo "Erreur interne du serveur";
            exit();
        }
    }
}

// 404 - Route non trouvée
http_response_code(404);
echo "Page non trouvée";
exit();