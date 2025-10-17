<?php
namespace Controllers\User;

use Controllers\ControllerInterface;
use Views\User\LoginView;
use Utilis\SessionService;

/**
 * Class User
 
 * @package     src

 * @subpackage  Controllers\User

 * @author      Benhafessa Alexandre, Dargentolle Francois, Edelstein William, Griguer Nathan, Radjou Dinesh

 * This class controls the login process (get).
 */
class Login implements ControllerInterface
{
    /**
     * Principal manager of the controller
     * 
     * @return void
     */
    public function control(): void
    {
        // Redirect to dashboard if already logged in
        if (SessionService::has('user_id')) {
            header('Location: /dashboard');
            exit();
        }

        $view = new LoginView();
        $view->render();
    }

    /**
     * Check if this controller can handle the request
     * 
     * @return boolean Is the method get?
     */
    public static function support(string $chemin, string $method): bool
    {
        return $chemin === "/login" && $method === "GET";
    }
}
