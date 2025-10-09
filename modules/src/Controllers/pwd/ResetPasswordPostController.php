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
            $updated = User::updatePasswordByEmail($tokenData['user_email'], $password);
            if (!$updated) {
                throw new ExceptionPasswordUpdateFailed("Erreur lors de la mise à jour du mot de passe.");
                error_log("Erreur mise à jour mot de passe pour: " . $tokenData['user_email']);
            }

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

        } catch (ExceptionValidationResetPassword $e) {
            SessionService::setFlash('errors', [$e->getMessage()]);

        } catch (ExceptionPasswordUpdateFailed $e) {
            SessionService::setFlash('errors', [$e->getMessage()]);

        } catch (\PDOException $e) {
            error_log("Erreur validation token: " . $e->getMessage());
            SessionService::setFlash('errors', ['Erreur lors de la validation du lien: veuillez réessayer plus tard.']);
            header('Location: /');
        } catch (\Throwable $e) {
            // generical fallback for unexpected errors
            SessionService::setFlash('errors', ["Une erreur inattendue est survenue."]);
            error_log("Erreur inattendue: " . $e->getMessage());
            header("Location: /forgot-password");
            exit();
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
