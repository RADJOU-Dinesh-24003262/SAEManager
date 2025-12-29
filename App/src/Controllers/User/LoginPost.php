<?php

namespace Controllers\User;

use Core\Controllers\ControllerInterface;
use Core\includes\exception\ExceptionBD\ExceptionFetchDataBD;
use Core\includes\exception\ExceptionValidation\ExceptionValidationEmptys;
use Core\includes\exception\ExceptionValidation\ExceptionValidationLogin;
use Core\Utilis\Logger;
use Core\Utilis\SessionService;
use Models\User\User;
use Validator\LoginValidator;
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

class LoginPost implements ControllerInterface
{
    /**
     * Principal manager of the controller
     *
     * @return void
     */
    #[\Override]
    public function control(): void
    {

        if (SessionService::has('user_id')) {
            header('Location: /dashboard');
            return;
        }

        // CSRF Protection.
        if (!SessionService::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            Logger::log('CSRF_FAIL', 'Tentative de connexion avec token invalide.', null, 'WARNING');
            SessionService::setFlash('errors', ['general' => 'Session invalide, veuillez réessayer.']);
            $view = new LoginView(['csrf_token' => SessionService::generateCsrfToken()]);
            $view->render();
            exit();
        }

        try {
            $validator = new LoginValidator();
            $data = $validator->escape($_POST);
            $validator->validate($data);

            $data['email'] = trim($data['email'] ?? '');
            Logger::log('LOGIN_ATTEMPT', "Tentative de connexion pour : {$data['email']}");

            $user = User::createFromLoginData($data);
            SessionService::regenerateId();

            SessionService::set('user_id', $user->getEmail());
            Logger::log('LOGIN_SUCCESS', "Connexion réussie pour : " . $user->getEmail(), $user->getUserId());
            SessionService::set('USER', serialize($user));

            header('Location: /dashboard');
            exit();
        } catch (ExceptionValidationEmptys $e) {
            $errors = [];
            foreach ($e->getErrors() as $error) {
                $errors[] = $error->getMessage();
            }
            SessionService::setFlash('errors', $errors);
        } catch (ExceptionValidationLogin $e) {
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
    #[\Override]
    public static function support(string $path, string $method): bool
    {
        return $path === "/login" && $method === "POST";
    }
}
