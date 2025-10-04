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
        $username = $_POST["username"];
        $password = $_POST["password"];
        if($this->login($username, $password)){
            $view = new IndexView();
            $view->render();
        }else{
            $view = new LoginView();
            $view->render();
        }

    }

    // A mettre dans le modèle
    public function login(string $username, string $password): bool
    {
        $connexion = database::getInstance();
        $str = "SELECT connection('$username', '$password')";
        $result = $connexion->query($str);
        $row = $result->fetch(PDO::FETCH_ASSOC);
        // Parser la chaîne : "(email,pwd,t)" -> extraire le dernier élément
        $data = trim($row['connection'], '()');
        $parts = explode(',', $data);
        $success = end($parts); // Récupère le dernier élément

        return $success === 't' || $success === 'true';
    }



    public static function support(string $chemin, string $method): bool
    {
        return $chemin === "/login" && $method === "POST";
    }
}