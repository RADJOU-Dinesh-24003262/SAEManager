<?php

namespace Controllers\Dashboard;

use Controllers\ControllerInterface;
use Views\Dashboard\DashboardView;
use Models\User\User;
use Utilis\SessionService;

/**
 * Controller responsible for handling the dashboard page.
 *
 * This class implements the ControllerInterface and provides
 * the logic required to render the dashboard view for an authenticated user.
 *
 * If the user is not authenticated (i.e., no user ID exists in the session),
 * they are redirected to the login page with a flash error message.
 *
 * @package Controllers\Dashboard
 */
class DashboardController implements ControllerInterface
{
    /**
     * Handles the logic to display the dashboard.
     *
     * This method:
     * - Checks if the user is authenticated via the session;
     * - Redirects to the login page with an error message if not;
     * - Retrieves the user object from the session;
     * - Passes the user data to the DashboardView and renders it.
     *
     * @return void
     */
    public function control(): void
    {
        if (!SessionService::has('user_id')) {
            SessionService::setFlash('errors', ['Vous devez vous authentifier avant d\'accéder à cette ressource']);
            header('Location: /login');
            return;
        }

        // Retrieve the user object stored in the session
        $user = unserialize(SessionService::get('USER'));

        $data['user'] = $user;

        // Create and render the dashboard view
        $view = new DashboardView($data);
        $view->render();
    }

    /**
     * Determines if this controller should handle the current request.
     *
     * This static method checks whether the controller supports
     * a given route path and HTTP method.
     *
     * @param string $path   The URL path of the request (e.g., "/dashboard")
     * @param string $method The HTTP method used (e.g., "GET", "POST")
     *
     * @return bool Returns true if the path is "/dashboard" and the method is "GET"; otherwise, false.
     */
    public static function support(string $path, string $method): bool
    {
        return $path === "/dashboard" && $method === "GET";
    }
}
