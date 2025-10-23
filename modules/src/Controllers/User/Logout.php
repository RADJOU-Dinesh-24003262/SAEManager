<?php

namespace Controllers\User;

use Controllers\ControllerInterface;
use Utilis\SessionService;
use Views\Index\IndexView;

/**
 * Logout controller.
 *
 * Handles user logout (GET request).
 *
 * @category Controller
 * @package  Controllers\User
 * @author   Radjou Dinesh <dinesh.radjou@etu.univ-amu.fr>
 * @license  MIT https://opensource.org/licenses/MIT
 * @link     https://github.com/RADJOU-Dinesh-24003262/SAEManager/
 */
class Logout implements ControllerInterface
{
    /**
     * Main controller logic for logout
     *
     * @return void
     */
    public function control(): void
    {
        // Redirect to login page if user is not logged in.
        if (!SessionService::has('user_id')) {
            SessionService::setFlash('errors', ['Veuillez vous connecter d\'abord']);
            header('Location: /login');
            exit();
        }

        SessionService::setFlash('success', 'Vous avez été bien déconnecté de votre session');

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
    public static function support(string $path, string $method): bool
    {
        return $path === "/logout" && strtoupper($method) === "GET";
    }
}
