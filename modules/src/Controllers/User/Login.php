<?php
namespace Controllers\User;

use Controllers\ControllerInterface;
use Views\User\LoginView;
use Utilis\SessionService;

class Login implements ControllerInterface
{
    public function control(): void
    {
        // Redirection si déjà connecté
        if (SessionService::has('user_id')) {
            header('Location: /dashboard');
            exit();
        }

        $view = new LoginView();
        $view->render();
    }

    public static function support(string $chemin, string $method): bool
    {
        return ($chemin === "/login" || $chemin === "/") && $method === "GET";
    }
}
