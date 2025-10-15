<?php
namespace Controllers\pwd;

use Controllers\ControllerInterface;
use includes\exception\ExceptionCreationTokenFailed;
use includes\exception\ExceptionEmailSendingFailed;
use Models\User\User;
use Utilis\TokenService;
use Utilis\EmailService;
use Utilis\SessionService;
use Utilis\Validator\ForgotPasswordValidator;
use Views\pwd\ForgotPasswordView;
use includes\exception\ExceptionValidationForgotPassword;
use includes\exception\ExceptionValidationEmptys;
use includes\exception\ExceptionSpam;

/**
 * Class ForgotPasswordPostController
 *
 * Handles the POST request to the "/forgot-password" route.
 * Validates form input, checks if a user exists, generates a reset token,
 * sends the reset email, and renders the view with appropriate feedback.
 *
 * @package Controllers\pwd
 * @version 1.0
 * @author Dinesh
 */

class ForgotPasswordPostController implements ControllerInterface
{
    /**
     * Main controller logic for processing the forgot password request.
     *
     * Steps:
     * - Validates and sanitizes the submitted form data.
     * - Checks if the user exists by email.
     * - If the user exists, generates a password reset token.
     * - Sends a password reset email with the token.
     * - Sets a generic success flash message (regardless of user existence).
     * - Catches and handles validation exceptions with appropriate error messages.
     * - Renders the ForgotPasswordView.
     *
     * @return void
     * @author Dinesh
     * @version 1.0
     */
    public function control(): void
    {
        try {
            // Validate the form data and avoid feature spam
            $validator = new ForgotPasswordValidator();
            $data = $validator->escape($_POST);
            $validator->validate($data);
            $email = trim($data['email'] ?? '');

            error_log("Demande réinitialisation pour: {$email}");

            // Verify if the user exists
            $userExists = User::existsByEmail($email);
            if ($userExists) {
                error_log("Utilisateur trouvé pour: {$email}");
                
                // Create the password reset token
                $token = TokenService::createPasswordResetToken($email);
                
                // Send the email
                EmailService::sendPasswordResetEmail($email, $token);
            }
            $_SESSION['last_forgot_password_request'] = time();

            // Generic message to avoid revealing if the email exists
            SessionService::setFlash('success',
                "Si cette adresse email est enregistrée dans notre système, " .
                "vous recevrez un lien de réinitialisation dans quelques minutes. " .
                "Vérifiez également vos courriers indésirables."
            );

        } catch (ExceptionValidationEmptys $e) {
            $errors = array_map(fn($error) => $error->getMessage(), $e->getErrors());
            SessionService::setFlash('errors', $errors);

        } catch (ExceptionValidationForgotPassword | ExceptionCreationTokenFailed | ExceptionEmailSendingFailed | ExceptionSpam $e) {
            SessionService::setFlash('errors', [$e->getMessage()]);

        }
        $view = new ForgotPasswordView();
        $view->render();
    }

    /**
     * Determines whether this controller supports a given route and method.
     *
     * @param string $chemin The route path (e.g., "/forgot-password").
     * @param string $method The HTTP method (e.g., "POST").
     * @return bool True if the controller should handle the request, false otherwise.
     */

    public static function support(string $path, string $method): bool
    {
        return $path === "/forgot-password" && $method === "POST";
    }
}