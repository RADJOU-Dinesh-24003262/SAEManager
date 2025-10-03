<?php
namespace Controllers\pwd;

use Controllers\ControllerInterface;
use Views\pwd\ForgotPasswordView;

class ForgotPasswordController implements ControllerInterface
{
    public function control(): void
    {
        $view = new ForgotPasswordView();
        $view->render();
    }

    public static function support(string $chemin, string $method): bool
    {
        return $chemin === "/forgot-password" && $method === "GET";
    }
}