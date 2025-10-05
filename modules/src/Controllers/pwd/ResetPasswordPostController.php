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
            // 1. Vérifie le token
            $token = $_GET['token'] ?? '';

            $tokenData = TokenService::validateToken($token);


            // 2. Validation des données
            $validator = new ResetPasswordValidator();
            $data = $validator->escape($_POST);
            $validator->validate($data);
            $password = $data['pwdnew'] ?? '';

            // 3. Mise à jour du mot de passe
            $updated = User::updatePasswordByEmail($tokenData['user_email'], $password);
            if (!$updated) {
                throw new ExceptionPasswordUpdateFailed("Erreur lors de la mise à jour du mot de passe.");
            }

            // 4. Marque le token comme utilisé
            TokenService::markTokenAsUsed($token);

            // 5. Affiche la page de succès
            (new ResetPasswordSuccessView())->render();
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
            // Fallback générique
            SessionService::setFlash('errors', ["Une erreur inattendue est survenue."]);
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
