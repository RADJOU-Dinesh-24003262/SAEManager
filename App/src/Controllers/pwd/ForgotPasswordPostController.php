<?php

namespace Controllers\pwd;

use Core\Controllers\ControllerInterface;
use Core\includes\exception\ExceptionEmailSendingFailed;
use Core\includes\exception\ExceptionSpam;
use Core\includes\exception\ExceptionToken\ExceptionCreationTokenFailed;
use Core\includes\exception\ExceptionValidation\ExceptionValidationEmptys;
use Core\includes\exception\ExceptionValidation\ExceptionValidationForgotPassword;
use Core\Utilis\SessionService;
use Models\User\User;
use Services\Auth\PasswordResetMailer;
use Services\TokenService;
use Validator\ForgotPasswordValidator;
use Views\pwd\ForgotPasswordView;

/**
 * Handles the POST request to the "/forgot-password" route.
 * Validates form input, checks if a user exists, generates a reset token,
 * sends the reset email, and renders the view with appropriate feedback.
 *
 * @category   Controllers
 * @package    Src
 * @subpackage Controllers/pwd

 * @author Alexandre Benhafessa <alexandre.benhafessa@etu.univ-amu.fr>
 * @author François Dargentolle <francois.dargentolle@etu.univ-amu.fr>
 * @author William Edelstein <william.edelstein@etu.univ-amu.fr>
 * @author Nathan Griguer <nathan.griguer@etu.univ-amu.fr>
 * @author Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>

 * @license MIT License https://opensource.org/licenses/MIT

 * @link https://github.com/RADJOU-Dinesh-24003262/SAEManager
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
     */
    #[\Override]
    public function control(): void
    {
        try {
            // Validate the form data and avoid feature spam.
            $validator = new ForgotPasswordValidator();
            $data = $validator->escape($_POST);
            $validator->validate($data);
            $email = trim($data['email'] ?? '');

            error_log("Demande réinitialisation pour: {$email}");

            // Verify if the user exists.
            $userExists = User::existsByEmail($email);
            if ($userExists) {
                error_log("Utilisateur trouvé pour: {$email}");

                // Create the password reset token.
                $token = TokenService::createPasswordResetToken($email);

                // Send the email.
                PasswordResetMailer::send($email, $token);
            }
            $_SESSION['last_forgot_password_request'] = time();

            // Generic message to avoid revealing if the email exists.
            SessionService::setFlash(
                'success',
                "Si cette adresse email est enregistrée dans notre système, " .
                "vous recevrez un lien de réinitialisation dans quelques minutes. " .
                "Vérifiez également vos courriers indésirables."
            );
        } catch (ExceptionValidationEmptys $e) {
            $errors = array_map(fn ($error) => $error->getMessage(), $e->getErrors());
            SessionService::setFlash('errors', $errors);
        } catch (
            ExceptionValidationForgotPassword | ExceptionCreationTokenFailed |
                                    ExceptionEmailSendingFailed | ExceptionSpam $e
        ) {
            SessionService::setFlash('errors', [$e->getMessage()]);
        }
        $view = new ForgotPasswordView();
        $view->render();
    }

    /**
     * Determines whether this controller supports a given route and method.
     *
     * @param  string $path   The route path (e.g., "/forgot-password").
     * @param  string $method The HTTP method (e.g., "POST").
     * @return boolean True if the controller should handle the request, false otherwise.
     */
    #[\Override]
    public static function support(string $path, string $method): bool
    {
        return $path === "/forgot-password" && $method === "POST";
    }
}
