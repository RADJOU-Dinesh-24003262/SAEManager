<?php

namespace Controllers\ToDoList;

use Core\ControllerInterface;
use Core\Utilis\SessionService;
use Exception;
use Models\User\User;
use Views\ToDoList\ToDoListView;

/**
 * Handles the control logic for the To-Do List page.
 *
 * @category   Controllers
 * @package    Src
 * @subpackage Controllers/ToDoList
 *
 * @author Alexandre Benhafessa <alexandre.benhafessa@etu.univ-amu.fr>
 * @author François Dargentolle <francois.dargentolle@etu.univ-amu.fr>
 * @author William Edelstein <william.edelstein@etu.univ-amu.fr>
 * @author Nathan Griguer <nathan.griguer@etu.univ-amu.fr>
 * @author Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 *
 * @license MIT License https://opensource.org/licenses/MIT
 *
 * @link https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class ToDoListController implements ControllerInterface
{
    /**
     * @method void control() Controls the rendering of the To-Do List view.
     *
     * @return void
     * @throws Exception If the user variable is not as expected.
     */
    public function control(): void
    {
        // Redirect to dashboard if already logged in.
        if (!SessionService::has('user_id')) {
            SessionService::setFlash('errors', ['Vous devez vous authentifier avant d\'accéder à cette ressource.']);
            header('Location: /');
            exit();
        }

        try {
            // Retrieve the user object stored in the session.
            $user = unserialize(SessionService::get('USER'));

            $data['user'] = $user;

            if (!$user || !($user instanceof User)) {
                throw new Exception('Unknown user');
            }

            $data['saes'] = $user->getSaes();

            $parts = explode('/', $_SERVER['REQUEST_URI']);
            $sae_id = $parts[2];

            foreach ($data['saes'] as $key => $sae) {
                if ($sae->getSaeSubjectId() == $sae_id) {
                    // Create and render the SAE page view.
                    $data['sae'] = $sae;
                    $view = new ToDoListView($data);
                    $view->render();
                    exit();
                }
            }

            header('Location: /');
        } catch (Exception $e) {
            SessionService::setFlash('errors', $e->getMessage());
            header('Location: /dashboard');
            exit();
        }
    }

    /**
     * Determines if this controller supports the given path and method.
     *
     * @method static bool support(string $path, string $method)
     * @param  string $path   Add the path to consult the page.
     * @param  string $method Add the kind of method to consult the page.
     * @return boolean True if the path and method are supported, false otherwise.
     */
    public static function support(string $path, string $method): bool
    {
        return preg_match('/^\/sae\/\d*\/to-do$/', $path) && $method === "GET";
    }
}
