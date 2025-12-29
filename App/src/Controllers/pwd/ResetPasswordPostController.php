<?php

namespace Controllers\pwd;

use Core\Controllers\ControllerInterface;
use Core\includes\exception\ExceptionPasswordUpdateFailed;
use Core\includes\exception\ExceptionToken\ExceptionInvalidToken;
use Core\includes\exception\ExceptionValidation\ExceptionValidationEmptys;
use Core\includes\exception\ExceptionValidation\ExceptionValidationResetPassword;
use Core\Utilis\SessionService;
use Models\User\User;
use Services\TokenService;
use Validator\ResetPasswordValidator;
use Views\pwd\ResetPasswordSuccessView;
use Views\pwd\ResetPasswordView;

/**
 * @category   Controller
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
class ResetPasswordPostController implements ControllerInterface
{
    /**
     * Principal manager of the controller
     *
     * @return void
     */
    #[\Override]
    public function control(): void
    {
        try {
            // Verify the token.
            $token = $_GET['token'] ?? '';

            $tokenData = TokenService::validateToken($token);


            // 2. Validate the data.
            $validator = new ResetPasswordValidator();
            $data = $validator->escape($_POST);
            $validator->validate($data);
            $password = $data['pwdnew'] ?? '';

            // 3. Update the password.
            User::updatePasswordByEmail($tokenData['email'], $password);

            // Mark the token as used.
            TokenService::markTokenAsUsed($token);

            // 5. Render the success page.
            (new ResetPasswordSuccessView())->render();
            error_log("Mot de passe réinitialisé avec succès pour: " . $tokenData['email']);
            return;
        } catch (ExceptionInvalidToken $e) {
            SessionService::setFlash('errors', [$e->getMessage()]);
            header("Location: /forgot-password");
            exit();
        } catch (ExceptionValidationEmptys $e) {
            $errors = array_map(fn ($error) => $error->getMessage(), $e->getErrors());
            SessionService::setFlash('errors', $errors);
        } catch (ExceptionValidationResetPassword | ExceptionPasswordUpdateFailed $e) {
            SessionService::setFlash('errors', [$e->getMessage()]);
        }
        $this->renderFormWithToken($_GET['token'] ?? '', $tokenData['email']);
    }

    /**
     * Allow to render ResetPasswordView with a specific token and email from the form
     *
     * @param  string      $token The request path.
     * @param  string|null $email The HTTP request method.
     * @return void
     */
    private function renderFormWithToken(string $token, ?string $email): void
    {
        (new ResetPasswordView($token, $email ? $email : ''))->render();
    }

    /**
     * Check if this controller can handle the request
     *
     * @param  string $path   The request path.
     * @param  string $method The HTTP request method.
     * @return boolean Is the method post?
     */
    #[\Override]
    public static function support(string $path, string $method): bool
    {
        return $path === "/reset-password" && $method === "POST";
    }
}
