<?php

namespace App\GUI\Controllers\pwd;

use Core\Controllers\ControllerInterface;
use Override;
use App\GUI\Views\pwd\ForgotPasswordView;

/**
 * This class controls the forgot password process (get).
 *
 * @category   Controllers
 * @package    Src
 * @subpackage Controllers/pwd
 * @author     Alexandre Benhafessa <alexandre.benhafessa@etu.univ-amu.fr>
 * @author     François Dargentolle <francois.dargentolle@etu.univ-amu.fr>
 * @author     William Edelstein <william.edelstein@etu.univ-amu.fr>
 * @author     Nathan Griguer <nathan.griguer@etu.univ-amu.fr>
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>

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
    #[Override]
    public function control(): void
    {
        $view = new ForgotPasswordView();
        $view->render();
    }

    /**
     * Check if this controller can handle the request
     *
     * @param  string $path   The request path.
     * @param  string $method The HTTP request method.
     * @return boolean Is the method get?
     */
    #[Override]
    public static function support(string $path, string $method): bool
    {
        return $path === "/forgot-password" && $method === "GET";
    }
}
