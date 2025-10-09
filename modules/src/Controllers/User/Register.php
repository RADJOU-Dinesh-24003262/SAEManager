<?php
namespace Controllers\User;

use Controllers\ControllerInterface;
use Utilis\SessionService;
use Views\User\RegisterView;

class Register implements ControllerInterface
{
    public function control(): void
    {

        if (SessionService::has('user_id')) {
            header('Location: /dashboard');
            exit();
        }

        $view = new RegisterView();
        $view->render();


    }

    public static function support(string $chemin, string $method): bool
    {
        return $chemin === "/register" && $method === "GET";
    }
}