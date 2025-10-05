<?php
namespace Controllers\pwd;

use Controllers\ControllerInterface;
use Models\User\User;
use Utilis\TokenService;
use Utilis\EmailService;
use Utilis\SessionService;
use Utilis\Validator\ForgotPasswordValidator;
use Views\pwd\ForgotPasswordView;

class ForgotPasswordPostController implements ControllerInterface
{
    public function control(): void
    {
        try {
            $validator = new ForgotPasswordValidator();
            $data = $validator->escape($_POST);
            $validator->validate($data);
            $email = trim($data['email'] ?? '');

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