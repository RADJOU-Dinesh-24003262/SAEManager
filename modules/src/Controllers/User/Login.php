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
        // Affichage initial du formulaire
        $view = new LoginView();
        $view->render();
    }

    public static function supportGet(string $chemin, string $method): bool
    {
        return $chemin === "/login" && $method === 'GET';
    }
    public static function support(string $chemin, string $method): bool
    {
        return self::supportGet($chemin, $method) ;
    }
}