<?php

namespace App\GUI\Controllers\ToDoList;

use App\Domain\ToDoList\ToDoList;
use App\Infrastructure\Persistence\Pdo\PdoToDoListRepository;
use App\GUI\Controllers\BaseController;
use App\Infrastructure\Service\SessionService;
use App\Domain\SAE\Exception\AccessDeniedException;
use Exception;
use App\Application\SAE\CanUserAccessSaeUseCase;
use App\Application\SAE\GetSaeDetailsUseCase;
use App\Application\SAE\GetUserSaesUseCase;
use App\Infrastructure\Persistence\Pdo\PdoSaeRepository;
use App\Infrastructure\Persistence\Pdo\PdoSaeGroupRepository;
use Override;
use App\GUI\Views\ToDoList\ToDoListView;

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
     * @throws AccessDeniedException If the user access to SAE is denied.
     */
    #[Override]
    public function control(): void
    {
        $this->ensureAuthenticated();

        try {
            $data['user'] = $this->user;

            $saeRepo = new PdoSaeRepository();
            $groupRepo = new PdoSaeGroupRepository();

            $getSaesUseCase = new GetUserSaesUseCase($saeRepo);
            $data['saes'] = $getSaesUseCase->execute($this->user);

            $parts = explode('/', $_SERVER['REQUEST_URI']);
            // Extract the SAE ID and remove any query parameters..
            $sae_id = intval(explode('?', $parts[2])[0]);

            $canAccessUseCase = new CanUserAccessSaeUseCase($saeRepo);
            if (!$canAccessUseCase->execute($this->user, $sae_id)) {
                throw new AccessDeniedException("Vous n'avez pas accès à cette SAE.");
            }

            $getDetailsUseCase = new GetSaeDetailsUseCase($saeRepo, $groupRepo);
            $data['sae'] = $getDetailsUseCase->execute($sae_id, $this->user);

            $currentGroupId = null;
            $tasks = [];
            $allGroups = [];

            if ($this->user->isStudent()) {
                // Students see their own group's todo list.
                if ($data['sae'] !== null && !empty($data['sae']['groups'])) {
                    // Assuming a student is in only one group per SAE.
                    $groupData = reset($data['sae']['groups']);

                    if ($groupData !== false) {
                        $currentGroupId = $groupData['group']->getId();
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
                        if ($groupData['group']->getId() == $selectedGroupId) {
                            $currentGroupId = $selectedGroupId;
                            break;
                        }
                    }
                }
            }

            // Fetch tasks if a group is identified.
            if ($currentGroupId) {
                $repo = new PdoToDoListRepository();
                $tasks = $repo->findByGroupId($currentGroupId);
            }

            $data['current_group_id'] = $currentGroupId;
            $data['tasks'] = $tasks;
            $data['all_groups'] = $allGroups;

            // Create and render the SAE page view.
            $view = new ToDoListView($data);
            $view->render();
            exit();
        } catch (AccessDeniedException $e) {
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