<?php

namespace Controllers\Register;

use Controllers\BaseController;
use Core\Includes\Exception\ExceptionCsrf;
use Core\Includes\Exception\ExceptionEmailAlreadyExists;
use Core\Includes\Exception\ExceptionSpam;
use Core\Includes\Exception\ExceptionValidation\ExceptionValidationEmptys;
use Core\Includes\Exception\ExceptionValidation\ExceptionValidationRegisters;
use Core\Utils\Logger;
use Core\Utils\SessionService;
use Exception;
use Models\Repository\User\PdoPendingRegistrationRepository;
use Models\Repository\User\PdoUserRepository;
use Models\UseCase\User\RegisterUserUseCase;
use Override;
use PDOException;
use Services\Auth\RegistrationMailer;
use Views\User\TwoFactorAuthentificationView;
use Views\User\RegisterPendingView;
use Validator\Register\ValidationServiceRegister;
use Views\User\RegisterSuccessView;
use Views\User\RegisterView;

/**
 * This class controls the register process (post).
 *
 * @category Controller
 *
 * @package Src
 *
 * @subpackage Controllers/User
 *
 * @author Alexandre Benhafessa <alexandre.benhafessa@etu.univ-amu.fr>
 * @author François Dargentolle <francois.dargentolle@etu.univ-amu.fr>
 * @author William Edelstein <william.edelstein@etu.univ-amu.fr>
 * @author Nathan Griguer <nathan.griguer@etu.univ-amu.fr>
 * @author Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 *
 * @license MIT License https://opensource.org/licenses/MIT
 *
 * @link https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class RegisterPostController extends BaseController
{
    /**
     * Principal manager of the controller
     *
     * @return void
     *
     * @throws Exception For any other unexpected errors during the registration process.
     */
    public function control(): void
    {
        // Validate the data.
        $validator = new ValidationServiceRegister();

        try {
            $this->checkCsrf('REGISTER');
            $this->checkHoneypot('REGISTER');

            $data = $validator->escape($_POST);
            $validator->validate($data);

            $userRepo    = new PdoUserRepository();
            $pendingRepo = new PdoPendingRegistrationRepository();

            $registerUseCase = new RegisterUserUseCase($userRepo, $pendingRepo);

            $token = $registerUseCase->execute($data);

            RegistrationMailer::send($data['email'], $token);

            Logger::log('REGISTER_ATTEMPTED', "New user registered in pending registrations: " . $data['email']);

            $vi²w = new TwoFactorAuthentificationView($data['email']);
            $view->render();
            exit();
        } catch (ExceptionEmailAlreadyExists $e) {
            SessionService::setFlash('errors', ['email' => $e->getMessage()]);
            Logger::log('REGISTER_FAIL', "Email already used: " . $e->getEmail(), null, 'INFO');
        } catch (ExceptionValidationRegisters | ExceptionValidationEmptys $e) {
            $errors = [];
            foreach ($e->getErrors() as $error) {
                $errors[] = $error->getMessage();
            }
            SessionService::setFlash('errors', $errors);
            Logger::log('REGISTER_FAIL', "Registration validation failed IP: {$_SERVER['REMOTE_ADDR']}", null, 'INFO');
        } catch (ExceptionCsrf | ExceptionSpam $e) {
            SessionService::setFlash('errors', ['general' => $e->getMessage()]);
        } catch (PDOException $e) {
            Logger::log('DB_ERROR', "Database error during registration: " . $e->getMessage(), null, 'CRITICAL');
            SessionService::setFlash('errors', ['general' => 'An error occurred, please try again later']);
        } catch (Exception $e) {
            Logger::log('REGISTER_ERROR', "Error during registration: " . $e->getMessage(), null, 'ERROR');
            SessionService::setFlash('errors', ['general' => 'Erreur lors de l\'inscription: ' . $e->getMessage()]);
        }
        $view = new RegisterView(['csrf_token' => SessionService::generateCsrfToken()]);
        $view->render();
    }
    /**
     * Determines whether this controller supports the given request.
     *
     * @param string $path   The requested URI path.
     * @param string $method The HTTP method used in the request.
     *
     * @return boolean True if the path is "/register" and the method is POST.
     */
    #[Override]
    public static function support(string $path, string $method): bool
    {
        return $path === "/register" && $method === "POST";
    }
}