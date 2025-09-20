<?php
namespace Controllers\User;

use Controllers\ControllerInterface;
use Views\User\RegisterView;

class Register implements ControllerInterface
{
    public function control(): void
    {
        $view = new RegisterView();
        $view->render();
    }

    public static function support(string $chemin, string $method): bool
    {
        return $chemin === "/register" && $method === "GET";
    }
}