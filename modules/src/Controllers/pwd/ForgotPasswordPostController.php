<?php
namespace Controllers\pwd;

use Controllers\ControllerInterface;
use Models\User\User;
use Utilis\TokenService;
use Utilis\EmailService;
use Utilis\SessionService;
use Views\pwd\ForgotPasswordView;

class ForgotPasswordPostController implements ControllerInterface
{
    public function control(): void
    {
        try {
            // Récupération et nettoyage de l'email
            $email = trim($_POST['email'] ?? '');
            
            // Validation basique
            if (empty($email)) {
                throw new \Exception("L'adresse email est requise.");
            }
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new \Exception("L'adresse email n'est pas valide.");
            }
            
            // Validation format AMU
            if (!preg_match('/^[a-zA-ZÀ-ÿ\-\']+\.[a-zA-ZÀ-ÿ\-\']+(\.[0-9]+)?@(etu\.)?univ-amu\.fr$/', $email)) {
                throw new \Exception("Veuillez utiliser votre adresse email universitaire AMU.");
            }
            
            // Vérifier si l'utilisateur existe (sans révéler dans le message)
            $userExists = User::existsByEmail($email);
            
            if ($userExists) {
                // Créer le token de réinitialisation
                $token = TokenService::createPasswordResetToken($email);
                
                if ($token === false) {
                    error_log("Échec création token pour: {$email}");
                } else {
                    // Envoyer l'email
                    $emailSent = EmailService::sendPasswordResetEmail($email, $token);
                    
                    if (!$emailSent) {
                        error_log("Échec envoi email à: {$email}");
                    }
                }
            }
            
            // Message générique pour ne pas révéler si l'email existe
            SessionService::setFlash('success', 
                "Si cette adresse email est enregistrée dans notre système, " .
                "vous recevrez un lien de réinitialisation dans quelques minutes. " .
                "Vérifiez également vos courriers indésirables."
            );
            
            // Redirection vers la même page
            header('Location: /forgot-password');
            exit();
            
        } catch (\Exception $e) {
            SessionService::setFlash('errors', [$e->getMessage()]);
            
            $view = new ForgotPasswordView();
            $view->render();
        }
    }

    public static function support(string $chemin, string $method): bool
    {
        return $chemin === "/forgot-password" && $method === "POST";
    }
}