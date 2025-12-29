<?php

namespace Controllers\SAE;

use Core\Controllers\ControllerInterface;
use Core\Utilis\SessionService;
use Exception;
use Models\User\Client;
use Models\User\User;
use Views\SAE\CreateSaeView;

/**
 * Controller to display the SAE creation form.
 *
 * @category   Controllers
 * @package    Src
 * @subpackage Controllers/SAE
 * @author     Dinesh Radjou <dinesh.radjou@univ-amu.fr>
 * @license    https://opensource.org/licenses/MIT MIT License
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager/blob/main/App/src/Controllers/SAE/CreateSaeController.php
 */
class CreateSaeController implements ControllerInterface
{
    /**
     * Controls the rendering of the SAE creation form.
     *
     * @return void
     * @throws Exception If an unknown user is encountered.
     */
    #[\Override]
    public function control(): void
    {

        // Redirect to /login if not logged in.
        if (!SessionService::has('user_id')) {
            SessionService::setFlash('errors', ['Authentification requise.']);
            header('Location: /login');
            exit();
        }

        // Retrieve the user object stored in the session.
        $user = unserialize(SessionService::get('USER'));

        $data['user'] = $user;

        if (!$user || !($user instanceof User)) {
            throw new Exception('Unknown user');
        }

        if (!$user->isProfessor()) {
            SessionService::setFlash('errors', ['Accès refusé.']);
            header('Location: /dashboard');
            exit();
        }

        $clients = Client::getAllClients();

        $view = new CreateSaeView(['clients' => $clients]);
        $view->render();
    }

    /**
     * Determines if this controller supports the given path and method.
     *
     * @param string $path   The requested path.
     * @param string $method The HTTP method.
     *
     * @return boolean
     */
    #[\Override]
    public static function support(string $path, string $method): bool
    {
        return $path === "/sae/create" && $method === "GET";
    }
}
