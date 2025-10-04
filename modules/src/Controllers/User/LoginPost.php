<?php
namespace Controllers\User;

use Controllers\ControllerInterface;
use includes\exception\ExceptionValidationLogin;
use Views\User\LoginView;
use Utilis\SessionService;

class LoginPost implements ControllerInterface
{
    public function control(): void
    {
        try {
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';

            error_log("Tentative de connexion - Username: '$username'");

            // Pour l'exemple, on utilise des utilisateurs "fictifs"
            $fakeUsers = [
                'admin' => 'admin',    // username => password
                'user' => 'password123'
            ];

            // VÉRIFICATION DE LA VALIDITÉ DES IDENTIFIANTS
            // Si l'utilisateur n'existe pas OU si le mot de passe est incorrect
            if (!isset($fakeUsers[$username]) || $fakeUsers[$username] !== $password) {
                // Déclenche l'exception si les identifiants sont invalides
                throw new ExceptionValidationLogin();
            }

            // Si les identifiants sont corrects
            SessionService::set('user_id', $username);
            header('Location: /dashboard');
            exit();

        } catch (ExceptionValidationLogin $e) {
            // 1. Capture l'exception.
            // 2. Stocke le message d'erreur dans les données de la vue.
            SessionService::setFlash('errors', ['general' => 'Erreur de connexion' . $e->getMessage()]);
            $view = new LoginView() ;
            $view->render();
            return;
        }
    }

    public static function supportPost(string $chemin, string $method): bool
    {
        return $chemin === "/login" && $method === 'POST';
    }
    public static function support(string $chemin, string $method): bool
    {
        return self::supportPost($chemin, $method);
    }
}