<?php
namespace Controllers\pwd;

use Controllers\ControllerInterface;
use Models\User\User;
use Utilis\TokenService;
use Utilis\SessionService;
use Views\pwd\ResetPasswordView;
use Views\pwd\ResetPasswordSuccessView;

class ResetPasswordPostController implements ControllerInterface
{
    public function control(): void
    {
        try {
            // Récupération des données
            $token = $_GET['token'] ?? '';
            $password = $_POST['pwdnew'] ?? '';
            $passwordConfirm = $_POST['pwdverif'] ?? '';

            // Validation du token
            if (empty($token)) {
                throw new \Exception("Token manquant.");
            }
            
            $tokenData = TokenService::validateToken($token);
            
            if ($tokenData === false) {
                throw new \Exception(
                    message: "Ce lien de réinitialisation est invalide ou a expiré. " .
                    "Veuillez faire une nouvelle demande."
                );
            }
            
            // Validation du mot de passe
            if (empty($password)) {
                throw new \Exception("Le mot de passe est requis.");
            }
            
            if (strlen($password) < 8) {
                throw new \Exception("Le mot de passe doit contenir au moins 8 caractères.");
            }
            
            if ($password !== $passwordConfirm) {
                throw new \Exception("Les mots de passe ne correspondent pas.");
            }
            
            // Mise à jour du mot de passe
            $updated = User::updatePasswordByEmail($tokenData['user_email'], $password);
            
            if (!$updated) {
                throw new \Exception("Erreur lors de la mise à jour du mot de passe.");
            }
            
            // Marquer le token comme utilisé
            TokenService::markTokenAsUsed($token);
            
            // Afficher la page de succès
            $view = new ResetPasswordSuccessView();
            $view->render();
            
        } catch (\Exception $e) {
            SessionService::setFlash('errors', [$e->getMessage()]);
            
            // Si on a le token et l'email, réafficher le formulaire
            $token = $_GET['token'] ?? '';
            if (!empty($token)) {
                $tokenData = TokenService::validateToken($token);
                if ($tokenData !== false) {
                    $view = new ResetPasswordView($token, $tokenData['user_email']);
                    $view->render();
                    return;
                }
            }
            
            // Sinon rediriger vers forgot-password
            header("Location: /forgot-password");
            exit();
        }
    }

    public static function support(string $chemin, string $method): bool
    {
        return $chemin === "/reset-password" && $method === "POST";
    }
}