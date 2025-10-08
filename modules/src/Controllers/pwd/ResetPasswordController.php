<?php
namespace Controllers\pwd;

use Controllers\ControllerInterface;
use Utilis\TokenService;
use Utilis\SessionService;
use Views\pwd\ResetPasswordView;

class ResetPasswordController implements ControllerInterface
{
    public function control(): void
    {
        // Get the token from the URL
        $token = $_GET['token'] ?? '';
        
        if (empty($token)) {
            SessionService::setFlash('errors', ['Lien de réinitialisation invalide.']);
            header('Location: /forgot-password');
            exit();
        }
        
        // Validate the token
        $tokenData = TokenService::validateToken($token);
        
        if ($tokenData === false) {
            SessionService::setFlash('errors', [
                'Ce lien de réinitialisation est invalide ou a expiré. ' .
                'Veuillez faire une nouvelle demande.'
            ]);
            header('Location: /forgot-password');
            exit();
        }
        
        // Token is valid, render the reset password view
        $view = new ResetPasswordView($token, $tokenData['user_email']);
        $view->render();
    }

    public static function support(string $chemin, string $method): bool
    {
        return $chemin === "/reset-password" && $method === "GET";
    }
}