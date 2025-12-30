<?php

namespace Controllers\ToDoList;

use Controllers\BaseController;
use Core\Utilis\SessionService;
use Exception;
use Models\SAE\SAE;
use Override;
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
class ToDoListController extends BaseController
{
    /**
     * @method void control() Controls the rendering of the To-Do List view.
     *
     * @return void
     * @throws Exception If the user variable is not as expected.
     */
    #[Override]
    public function control(): void
    {
        $this->ensureAuthenticated();

        try {
            $data['user'] = $this->user;
            $data['saes'] = $this->user->getSaes();

            $parts = explode('/', $_SERVER['REQUEST_URI']);
            $sae_id = intval($parts[2]);

            $sae = SAE::getInstance();
            $data['sae'] = $sae->getCompleteSAEData($sae_id, $this->user);
            if ($data['sae'] === null) {
                throw new Exception("Vous n\'avez pas accès à cette SAE.");
            }

            // Create and render the SAE page view.
            $view = new ToDoListView($data);
            $view->render();
            exit();
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
    #[Override]
    public static function support(string $path, string $method): bool
    {
        return preg_match('/^\/sae\/\d*\/to-do$/', $path) && $method === "GET";
    }
}
