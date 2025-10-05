<?php
namespace Controllers\User;

use Controllers\ControllerInterface;
use includes\exception\ExceptionValidationLogin;
use Views\User\LoginView;
use Utilis\SessionService;
use Utilis\Validator\LoginValidator;

class LoginPost implements ControllerInterface
{
    public function control(): void
    {
        try {
            $validator = new LoginValidator();
            $data = $validator->escape($_POST);
            $validator->validate($data);

            $username = trim($data['username'] ?? '');
            $password = $data['password'] ?? '';

            error_log("Tentative de connexion - Username: '$username'");

            // Pour l'exemple, on utilise des utilisateurs "fictifs"
            $fakeUsers = [
                'admin' => 'admin',    // username => password
                'user' => 'password123'
            ];

            // VÉRIFICATION DE LA VALIDITÉ DES IDENTIFIANTS
            if (!isset($fakeUsers[$username]) || $fakeUsers[$username] !== $password) {
                throw new ExceptionValidationLogin();
            }

            // Si les identifiants sont corrects
            SessionService::set('user_id', $username);
            header('Location: /dashboard');
            exit();

        } catch (ExceptionValidationLogin $e) {
            SessionService::setFlash('errors', ['general' => 'Erreur de connexion ' . $e->getMessage()]);
            $view = new LoginView();
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