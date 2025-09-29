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

        // Gestion du formulaire
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // ✅ CORRIGÉ : Nettoyage des inputs avec trim()
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';

            // ✅ CORRIGÉ : Debug plus propre (optionnel - à supprimer en production)
            error_log("Tentative de connexion - Username: '$username'");

            // Pour l'exemple, on utilise des utilisateurs "fictifs"
            $fakeUsers = [
                'admin' => 'admin',    // username => password
                'user' => 'password123' // autre utilisateur de test
            ];

            if (isset($fakeUsers[$username]) && $fakeUsers[$username] === $password) {
                // On stocke l'ID utilisateur en session
                SessionService::set('user_id', $username);
                header('Location: /dashboard');
                exit();
            } else {
                // Erreur → on recharge la vue avec un message
                $view = new LoginView(['error' => 'Identifiants invalides']);
                $view->render();
                return;
            }
        }

        // Affichage initial du formulaire
        $view = new LoginView();
        $view->render();
    }

    public static function support(string $chemin, string $method): bool
    {
        // On gère GET (formulaire) et POST (soumission)
        return $chemin === "/login" && in_array($method, ['GET', 'POST']);
    }
}