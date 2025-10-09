<?php
namespace Controllers\pwd;

use Controllers\ControllerInterface;
use Models\User\User;
use Utilis\TokenService;
use Utilis\EmailService;
use Utilis\SessionService;
use Utilis\Validator\ForgotPasswordValidator;
use Views\pwd\ForgotPasswordView;
use includes\exception\ExceptionValidationForgotPassword;
use includes\exception\ExceptionValidationEmptys;

class ForgotPasswordPostController implements ControllerInterface
{
    public function control(): void
    {
        try {
            $validator = new ForgotPasswordValidator();
            $data = $validator->escape($_POST);
            $validator->validate($data);
            $email = trim($data['email'] ?? '');

            error_log("Demande réinitialisation pour: {$email}");

            error_log("Demande réinitialisation pour: {$email}");

            // Verify if the user exists
            $userExists = User::existsByEmail($email);
            if ($userExists) {
                error_log("Utilisateur trouvé pour: {$email}");
                
                // Create the password reset token
                $token = TokenService::createPasswordResetToken($email);
                if ($token === false) {
                    error_log("Échec création token pour: {$email}");
                } else {
                    // Send the email
                    $emailSent = EmailService::sendPasswordResetEmail($email, $token);
                    if (!$emailSent) {
                        error_log("Échec envoi email à: {$email}");
                    }
                }
            }

            // Generic message to avoid revealing if the email exists
            SessionService::setFlash('success',
                "Si cette adresse email est enregistrée dans notre système, " .
                "vous recevrez un lien de réinitialisation dans quelques minutes. " .
                "Vérifiez également vos courriers indésirables."
            );

        } catch (ExceptionValidationEmptys $e) {
            $errors = array_map(fn($error) => $error->getMessage(), $e->getErrors());
            SessionService::setFlash('errors', $errors);

        } catch (ExceptionValidationForgotPassword $e) {
            SessionService::setFlash('errors', [$e->getMessage()]);
        }
        $view = new ForgotPasswordView();
        $view->render();
    }

    public static function support(string $chemin, string $method): bool
    {
        return $chemin === "/forgot-password" && $method === "POST";
    }
}