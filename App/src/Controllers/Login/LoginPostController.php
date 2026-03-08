<?php

namespace Controllers\Login;

use Controllers\BaseController;
use Core\Includes\Exception\ExceptionBD\ExceptionFetchDataBD;
use Core\Includes\Exception\ExceptionValidation\ExceptionValidationEmptys;
use Core\Includes\Exception\ExceptionValidation\ExceptionValidationLogin;
use Core\Utils\Logger;
use Core\Utils\RateLimiter;
use Core\Utils\SessionService;
use Models\Entity\User\User;
use Models\Repository\User\PdoUserRepository;
use Models\UseCase\User\LoginUseCase;
use Override;
use Validator\Login\LoginValidator;
use Views\User\LoginView;

/**
 * This class controls the login process (post).

 * @category Controller

 * @package Src

 * @subpackage Controllers/User

 * @author Alexandre Benhafessa <alexandre.benhafessa@etu.univ-amu.fr>
 * @author François Dargentolle <francois.dargentolle@etu.univ-amu.fr>
 * @author William Edelstein <william.edelstein@etu.univ-amu.fr>
 * @author Nathan Griguer <nathan.griguer@etu.univ-amu.fr>
 * @author Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>

 * @license MIT License https://opensource.org/licenses/MIT

 * @link https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */

class LoginPostController extends BaseController
{
    /**
     * Principal manager of the controller
     *
     * @return void
     */
    public function control(): void
    {

        if (SessionService::has('user_id')) {
            header('Location: /dashboard');
            return;
        }

        $this->checkCsrf('LOGIN', '/login');

        $data = [];

        try {
            $validator = new LoginValidator();
            $data = $validator->escape($_POST);
            $validator->validate($data);

            $data['email'] = trim($data['email'] ?? '');
            Logger::log('LOGIN_ATTEMPT', "Tentative de connexion pour : {$data['email']}");

            $userRepository = new PdoUserRepository();
            $loginUseCase = new LoginUseCase($userRepository);
            $user = $loginUseCase->execute($data['email'], $data['password']);

            SessionService::regenerateId();

            SessionService::set('user_id', $user->getEmail());
            Logger::log('LOGIN_SUCCESS', "Connexion réussie pour : " . $user->getEmail(), $user->getUserId());
            SessionService::set('USER', serialize($user));
            RateLimiter::clear('login');

            header('Location: /dashboard');
            exit();
        } catch (ExceptionValidationEmptys $e) {
            $errors = [];
            foreach ($e->getErrors() as $error) {
                $errors[] = $error->getMessage();
            }
            SessionService::setFlash('errors', $errors);
        } catch (ExceptionValidationLogin $e) {
            RateLimiter::increment('login');
            Logger::log('LOGIN_FAIL', "Échec authentification pour : {$data['email']}", null, 'WARNING');
            SessionService::setFlash('errors', ['general' => 'Erreur de connexion : ' . $e->getMessage()]);
        } catch (ExceptionFetchDataBD $e) {
            Logger::log('DB_ERROR', "Erreur BDD lors du login : " . $e->getMessage(), null, 'CRITICAL');
            SessionService::setFlash('errors', ['general' => 'Erreur technique.']);
        }
        $view = new LoginView(['csrf_token' => SessionService::generateCsrfToken()]);
        $view->render();
    }

    /**
     * Check if this controller can handle the request
     *
     * @param string $path   The requested URI path.
     * @param string $method The HTTP method used in the request.
     *
     * @return boolean True if the path is "/login" and the method is POST.
     */
    #[Override]
    public static function support(string $path, string $method): bool
    {
        return $path === "/login" && $method === "POST";
    }
}
