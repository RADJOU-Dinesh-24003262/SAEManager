<?php

namespace Controllers\User;

use Controllers\ControllerInterface;
use Utilis\SessionService;
use Views\User\RegisterView;

/**
 * Class User

 * @package     src

 * @subpackage  Controllers\User

 * @author      Benhafessa Alexandre, Dargentolle Francois, Edelstein William, Griguer Nathan, Radjou Dinesh

 * This class controls the register process (get).
 */
class Register implements ControllerInterface
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
            exit();
        }

        $view = new RegisterView();
        $view->render();
    }

    /**
     * Check if this controller can handle the request
     *
     * @return boolean Is the method get?
     */
    public static function support(string $chemin, string $method): bool
    {
        return $chemin === "/register" && $method === "GET";
    }
}
