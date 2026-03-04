<?php

namespace Controllers\ResetPassword;

use Controllers\BaseController;
use Core\includes\exception\ExceptionPasswordUpdateFailed;
use Core\includes\exception\ExceptionToken\ExceptionInvalidToken;
use Core\includes\exception\ExceptionValidation\ExceptionValidationEmptys;
use Core\includes\exception\ExceptionValidation\ExceptionValidationResetPassword;
use Core\Utilis\SessionService;
use Models\Entity\User\User;
use Models\Repository\User\PdoUserRepository;
use Models\UseCase\User\HandlePasswordResetUseCase;
use Models\UseCase\User\ResetPasswordUseCase;
use Override;
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
class ResetPasswordPostController extends BaseController
{
    /**
     * Principal manager of the controller
     *
     * @return void
     */
    public function control(): void
    {
        $email = '';
        try {
            $token = $_GET['token'] ?? '';

            $validator = new ResetPasswordValidator();
            $data = $validator->escape($_POST);
            $validator->validate($data);
            $password = $data['pwdnew'] ?? '';

            $handlePasswordResetUseCase = new HandlePasswordResetUseCase(new PdoUserRepository());
            $email = $handlePasswordResetUseCase->execute($token, $password);

            (new ResetPasswordSuccessView())->render();
            error_log("Mot de passe réinitialisé avec succès pour: " . $email);
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

        $view = new ResetPasswordView($_GET['token'] ?? '', $email);
        $view->render();
    }

    /**
     * Check if this controller can handle the request
     *
     * @param  string $path   The request path.
     * @param  string $method The HTTP request method.
     * @return boolean Is the method post?
     */
    #[Override]
    public static function support(string $path, string $method): bool
    {
        return $path === "/reset-password" && $method === "POST";
    }
}
