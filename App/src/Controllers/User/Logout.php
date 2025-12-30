<?php

namespace Controllers\User;

use Core\Controllers\ControllerInterface;
use Core\Utilis\SessionService;
use Override;
use Views\Index\IndexView;

/**
 * Logout controller.
 *
 * Handles user logout (GET request).
 *
 * @category Controller
 * @package  Controllers\User
 * @author   Alexandre Benhafessa <alexandre.benhafessa@etu.univ-amu.fr>
 * @author   François Dargentolle <francois.dargentolle@etu.univ-amu.fr>
 * @author   William Edelstein <william.edelstein@etu.univ-amu.fr>
 * @author   Nathan Griguer <nathan.griguer@etu.univ-amu.fr>
 * @author   Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license  MIT https://opensource.org/licenses/MIT
 * @link     https://github.com/RADJOU-Dinesh-24003262/SAEManager/
 */
class Logout implements ControllerInterface
{
    /**
     * Main controller logic for logout.
     *
     * @return void
     */
    #[Override]
    public function control(): void
    {
        // Redirect to login page if user is not logged in.
        if (!SessionService::has('user_id')) {
            header('Location: /');
            exit();
        }

        SessionService::setFlash('success', 'Vous avez été bien déconnecté de votre session');

        // Unset User session.
        SessionService::remove('user_id');

        // Render logout view.
        $view = new IndexView();
        $view->render();


        // Clear session.
        session_unset();     // Unset all session variables.
        session_destroy();   // Destroy the session.
    }

    /**
     * Check if this controller should handle the current request
     *
     * @param  string $path   The request path.
     * @param  string $method The HTTP request method.
     * @return boolean True if path is /logout and method is GET.
     */
    #[Override]
    public static function support(string $path, string $method): bool
    {
        return $path === "/logout" && strtoupper($method) === "GET";
    }
}
