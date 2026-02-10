<?php

namespace App\GUI\Controllers\pwd;

use App\Application\Email\PasswordResetMailer;
use Core\Controllers\ControllerInterface;
use App\Infrastructure\Security\InputSanitizer;
use App\Domain\User\Exception\EmailAlreadyExistsException;
use App\Domain\User\Exception\SpamException;
use App\Infrastructure\Exception\TokenCreationException;
use App\Application\Validation\Exception\EmptyFieldsException;
use App\Application\Validation\Exception\ForgotPasswordValidationException;
use App\Infrastructure\Service\SessionService;
use App\Application\User\CheckUserExistsUseCase;
use App\Infrastructure\Persistence\Pdo\PdoUserRepository;
use Override;
use App\Infrastructure\Service\TokenService;
use \App\Application\Validation\User\ForgotPasswordValidator;
use App\GUI\Views\pwd\ForgotPasswordView;

/**
 * Handles the POST request to the "/forgot-password" route.
 * Validates form input, checks if a user exists, generates a reset token,
 * sends the reset email, and renders the view with appropriate feedback.
 *
 * @category   Controllers
 * @package    Src
 * @subpackage Controllers/pwd
 * @author     Alexandre Benhafessa <alexandre.benhafessa@etu.univ-amu.fr>
 * @author     François Dargentolle <francois.dargentolle@etu.univ-amu.fr>
 * @author     William Edelstein <william.edelstein@etu.univ-amu.fr>
 * @author     Nathan Griguer <nathan.griguer@etu.univ-amu.fr>
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
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
    #[Override]
    public function control(): void
    {
        try {
            // Validate the form data and avoid feature spam.
            $validator = new ForgotPasswordValidator();
            // Sanitization now done before validation
            $data = InputSanitizer::sanitize($_POST);
            $validator->validate($data);
            $email = trim($data['email'] ?? '');

            error_log("Demande réinitialisation pour: {$email}");

            // Verify if the user exists.
            $useCase = new CheckUserExistsUseCase(new PdoUserRepository());
            $userExists = $useCase->execute($email);

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
        } catch (EmptyFieldsException $e) {
            $errors = array_map(fn ($error) => $error->getMessage(), $e->getErrors());
            SessionService::setFlash('errors', $errors);
        } catch (
            ForgotPasswordValidationException | TokenCreationException |
                                    EmailAlreadyExistsException | SpamException $e
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
    #[Override]
    public static function support(string $path, string $method): bool
    {
        return $path === "/forgot-password" && $method === "POST";
    }
}
