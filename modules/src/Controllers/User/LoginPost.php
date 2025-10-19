<?php

namespace Controllers\User;

use Controllers\ControllerInterface;
use includes\exception\ExceptionValidationLogin;
use includes\exception\ExceptionValidationEmptys;
use includes\database;
use includes\exception\ExceptionFetchDataBD;
use PDO;
use Models\User\User;
use Utilis\ValidationServiceRegister;
use Utilis\SessionService;
use Views\Index\IndexView;
use Views\User\LoginView;
use Utilis\Validator\LoginValidator;

/**
 * Class User
 
 * @package     src

 * @subpackage  Controllers\User

 * @author      Benhafessa Alexandre, Dargentolle Francois, Edelstein William, Griguer Nathan, Radjou Dinesh

 * This class controls the login process (post).
 */
class LoginPost implements ControllerInterface
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

        try {
            $validator = new LoginValidator();
            $data = $validator->escape($_POST);
            $validator->validate($data);

            $data['email'] = trim($data['email'] ?? '');
            error_log("Tentative de connexion - Username: {$data['email']}");

            $user = User::createFromLoginData($data);
            SessionService::regenerateId();

            SessionService::set('user_id', $user->getEmail());
            error_log("Utilisateur connecté: " . $user->getEmail());
            SessionService::set('USER', serialize($user));

            header('Location: /dashboard');
            exit();
        } catch (ExceptionValidationEmptys $e) {
            $errors = [];
            foreach ($e->getErrors() as $error) {
                $errors[] = $error->getMessage();
            }
            SessionService::setFlash('errors', $errors);
        } catch (ExceptionValidationLogin | ExceptionFetchDataBD $e) {
            SessionService::setFlash('errors', ['general' => 'Erreur de connexion : ' . $e->getMessage()]);
        }
        $view = new LoginView();
        $view->render();
    }

    /**
     * Check if this controller can handle the request
     * 
     * @return boolean Is the method post?
     */
    public static function support(string $chemin, string $method): bool
    {
        return $chemin === "/login" && $method === "POST";
    }
}
