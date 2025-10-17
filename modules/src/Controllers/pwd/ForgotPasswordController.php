<?php
namespace Controllers\pwd;

use Controllers\ControllerInterface;
use Views\pwd\ForgotPasswordView;

/**
 * Class User
 
 * @package     src

 * @subpackage  Controllers\pwd

 * @author      Benhafessa Alexandre, Dargentolle Francois, Edelstein William, Griguer Nathan, Radjou Dinesh

 * This class controls the forgot password process (get).
 */
class ForgotPasswordController implements ControllerInterface
{
    /**
     * Principal manager of the controller
     * 
     * @return void
     */
    public function control(): void
    {
        $view = new ForgotPasswordView();
        $view->render();
    }

    /**
     * Check if this controller can handle the request
     * 
     * @return boolean Is the method get?
     */
    public static function support(string $chemin, string $method): bool
    {
        return $chemin === "/forgot-password" && $method === "GET";
    }
}