<?php
namespace Controllers\User;

use Controllers\ControllerInterface;
use includes\exception\ExceptionValidationLogin;
use includes\exception\ExceptionValidationEmptys;
use includes\database;
use PDO;
use Models\User\User;
use Utilis\ValidationServiceRegister;
use Utilis\SessionService;
use Views\Index\IndexView;
use Views\User\LoginView;
use Utilis\Validator\LoginValidator;

class LoginPost implements ControllerInterface{
    public function control(): void
    {

        if (SessionService::has('user_id')) {
            header('Location: /dashboard');
            exit();
        }

        try {
            $validator = new LoginValidator();
            $data = $validator->escape($_POST);
            $validator->validate($data);

            $data['email'] = trim($data['email'] ?? '');
            $data['password'] = $data['password'] ?? '';

            error_log("Tentative de connexion - Username: {$data['email']}");

            $user = User::createFromLoginData($data);

            SessionService::set('user_id', $user->getEmail());
            error_log("Utilisateur connecté: " . $user->getEmail());

            header('Location: /dashboard');
            exit();


        }catch(ExceptionValidationEmptys $e){
            $errors = [];
            foreach ($e->getErrors() as $error) {
                $errors[] = $error->getMessage();
            }
            SessionService::setFlash('errors', $errors);

        } catch (ExceptionValidationLogin $e) {
            SessionService::setFlash('errors', ['general' => 'Erreur de connexion : ' . $e->getMessage()]);
        }
        $view = new LoginView();
        $view->render();
    }



    public static function support(string $chemin, string $method): bool
    {
        return $chemin === "/login" && $method === "POST";
    }
}