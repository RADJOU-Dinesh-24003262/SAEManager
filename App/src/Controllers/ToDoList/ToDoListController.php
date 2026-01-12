<?php

namespace Controllers\ToDoList;

use App\Models\ToDoList\ToDoList;
use Controllers\BaseController;
use Core\Utilis\SessionService;
use Core\includes\exception\SAE\ExceptionAccessDenied;
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
     * @throws ExceptionAccessDenied If the user access to SAE is denied.
     */
    #[Override]
    public function control(): void
    {
        $this->ensureAuthenticated();

        try {
            $data['user'] = $this->user;
            $data['saes'] = $this->user->getSaes();

            $parts = explode('/', $_SERVER['REQUEST_URI']);
            // Extract the SAE ID and remove any query parameters..
            $sae_id = intval(explode('?', $parts[2])[0]);

            if (!$this->user->canAccessSAE($sae_id)) {
                throw new ExceptionAccessDenied("Vous n'avez pas accès à cette SAE.");
            }

            $sae = SAE::getInstance();
            // Fetch complete SAE data to check access and get group info.
            $data['sae'] = $sae->getCompleteSAEData($sae_id, $this->user);

            $currentGroupId = null;
            $tasks = [];
            $allGroups = [];

            if ($this->user->isStudent()) {
                // Students see their own group's todo list.
                if ($data['sae'] !== null && !empty($data['sae']['groups'])) {
                    // Assuming a student is in only one group per SAE.
                    $groupData = reset($data['sae']['groups']);

                    if ($groupData !== false) {
                        $currentGroupId = $groupData['group']->getSaeGroupId();
                    }
                }
            } elseif ($this->user->isProfessor()) {
                // Professors see all groups they manage (or all if responsible).
                $allGroups = $data['sae']['groups'] ?? [];

                // Check if a specific group is selected via GET parameter.
                if (isset($_GET['group_id'])) {
                    $selectedGroupId = intval($_GET['group_id']);
                    // Verify if the professor has access to this group.
                    foreach ($allGroups as $groupData) {
                        if ($groupData['group']->getSaeGroupId() == $selectedGroupId) {
                            $currentGroupId = $selectedGroupId;
                            break;
                        }
                    }
                }
            }

            // Fetch tasks if a group is identified.
            if ($currentGroupId) {
                $tasks = ToDoList::getAllTasks($currentGroupId);
            }

            $data['current_group_id'] = $currentGroupId;
            $data['tasks'] = $tasks;
            $data['all_groups'] = $allGroups;

            // Create and render the SAE page view.
            $view = new ToDoListView($data);
            $view->render();
            exit();
        } catch (ExceptionAccessDenied $e) {
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
