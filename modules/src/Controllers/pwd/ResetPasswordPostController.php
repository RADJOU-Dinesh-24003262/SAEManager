<?php
namespace Controllers\pwd;

use Controllers\ControllerInterface;
use Models\User\User;
use Utilis\TokenService;
use Utilis\SessionService;
use Utilis\Validator\ResetPasswordValidator;
use Views\pwd\ResetPasswordView;
use Views\pwd\ResetPasswordSuccessView;

use includes\exception\ExceptionValidationResetPassword;
use includes\exception\ExceptionValidationEmptys;
use includes\exception\ExceptionInvalidToken;
use includes\exception\ExceptionPasswordUpdateFailed;

class ResetPasswordPostController implements ControllerInterface
{
    public function control(): void
    {
        try {
            // Verify the token
            $token = $_GET['token'] ?? '';

            $tokenData = TokenService::validateToken($token);


            // 2. Validate the data
            $validator = new ResetPasswordValidator();
            $data = $validator->escape($_POST);
            $validator->validate($data);
            $password = $data['pwdnew'] ?? '';

            // 3. Update the password
            User::updatePasswordByEmail($tokenData['user_email'], $password);

            // Mark the token as used
            TokenService::markTokenAsUsed($token);

            // 5. Render the success page
            (new ResetPasswordSuccessView())->render();
            error_log("Mot de passe réinitialisé avec succès pour: " . $tokenData['user_email']);
            return;

        } catch (ExceptionInvalidToken $e) {
            SessionService::setFlash('errors', [$e->getMessage()]);
            header("Location: /forgot-password");
            exit();

        } catch (ExceptionValidationEmptys $e) {
            $errors = array_map(fn($error) => $error->getMessage(), $e->getErrors());
            SessionService::setFlash('errors', $errors);

        } catch (ExceptionValidationResetPassword | ExceptionPasswordUpdateFailed $e) {
            SessionService::setFlash('errors', [$e->getMessage()]);
        }
        $this->renderFormWithToken($_GET['token'] ?? '', $tokenData['user_email'] ?? null);

    }

    private function renderFormWithToken(string $token, ?string $email): void
    {
        (new ResetPasswordView($token, $email))->render();
    }

    public static function support(string $chemin, string $method): bool
    {
        return $chemin === "/reset-password" && $method === "POST";
    }
}
