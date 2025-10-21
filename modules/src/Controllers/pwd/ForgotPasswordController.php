<?php

namespace Controllers\pwd;

use Controllers\ControllerInterface;
use Views\pwd\ForgotPasswordView;

/**
 * Class User
 * This class controls the forgot password process (get).

 * @package Src

 * @subpackage Controllers\pwd

 * @author  Alexandre Benhafessa <alexandre.benhafessa@etu.univ-amu.fr>
 * @author  François Dargentolle <francois.dargentolle@etu.univ-amu.fr>
 * @author  William Edelstein <william.edelstein@etu.univ-amu.fr>
 * @author  Nathan Griguer <nathan.griguer@etu.univ-amu.fr>
 * @author  Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>

 * @license MIT License https://opensource.org/licenses/MIT

 * @link https://github.com/RADJOU-Dinesh-24003262/SAEManager
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
