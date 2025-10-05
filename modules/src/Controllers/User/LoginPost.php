<?php
namespace Controllers\User;

use Controllers\ControllerInterface;
use includes\database;
use PDO;
use Models\User\User;
use Utilis\ValidationServiceRegister;
use Utilis\SessionService;
use Views\Index\IndexView;
use Views\User\LoginView;
use Views\User\RegisterView;
use Views\User\RegisterSuccessView;
use includes\exception\ExceptionValidationRegisters;

class LoginPost implements ControllerInterface{
    public function control(): void
    {


        if (SessionService::has('user_id')) {
            header('Location: /dashboard');
            exit();
        }

        $email = $_POST["username"];
        $password = $_POST["password"];
        $user = new User(email: $email);
        $user->setPassword($password);

        if($user->login()){
            SessionService::set('user_id', $user->getEmail());

            header('Location: /dashboard');
            exit();
        }else{
            $view = new LoginView();
            $view->render();
        }

    }




    public static function support(string $chemin, string $method): bool
    {
        return $chemin === "/login" && $method === "POST";
    }
}