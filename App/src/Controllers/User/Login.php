<?php

namespace Controllers\User;

use core\ControllerInterface;
use Views\User\LoginView;
use core\Utilis\SessionService;

/**
 * Class Login (GET)
 * This class controls the login process (get).

 * @category Controller

 * @package Src

 * @subpackage Controllers\User

 * @author  Alexandre Benhafessa <alexandre.benhafessa@etu.univ-amu.fr>
 * @author  François Dargentolle <francois.dargentolle@etu.univ-amu.fr>
 * @author  William Edelstein <william.edelstein@etu.univ-amu.fr>
 * @author  Nathan Griguer <nathan.griguer@etu.univ-amu.fr>
 * @author  Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 *
 * @license MIT License https://opensource.org/licenses/MIT

 * @link https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class Login implements ControllerInterface
{
    /**
     * Principal manager of the controller.
     *
     * @return void
     */
    public function control(): void
    {
        // Redirect to dashboard if already logged in.
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
     * @param string $path   The request URI path.
     * @param string $method The HTTP request method (e.g., GET, POST).
     *
     * @return boolean True if the request is a GET to "/login", false otherwise.
     */
    public static function support(string $path, string $method): bool
    {
        return $path === "/login" && $method === "GET";
    }
}
