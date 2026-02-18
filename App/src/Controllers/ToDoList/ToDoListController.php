<?php

namespace Controllers\ToDoList;

use Models\Entity\ToDoList\ToDoList;
use Models\Repository\ToDoList\PdoToDoListRepository;
use Models\UseCase\ToDoList\GetTasksUseCase;
use Controllers\BaseController;
use Core\Utilis\SessionService;
use Core\includes\exception\SAE\ExceptionAccessDenied;
use Exception;
use Override;
use Views\ToDoList\ToDoListView;
use Models\Repository\SAE\{PdoSAESubjectRepository, PdoSAEGroupRepository, PdoParticipatedInRepository};
use Models\Repository\User\{PdoStudentRepository, PdoProfessorRepository, PdoClientRepository};

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
     * Controls the rendering of the To-Do List view.
     *
     * @return void
     * @throws Exception If the SAE doesn't exist.
     */
    #[Override]
    public function control(): void
    {
        $this->ensureAuthenticated();

        try {
            $parts = explode('/', $_SERVER['REQUEST_URI']);
            // Extract the SAE ID and remove any query parameters.
            $sae_id = intval(explode('?', $parts[2])[0]);

            $userId = $this->user->getUserId();

            $saeSubjectRepo = new PdoSAESubjectRepository();
            $saeGroupRepo = new PdoSAEGroupRepository();
            $participatedInRepo = new PdoParticipatedInRepository();

            $currentGroupId = null;
            $tasks = [];

            // Fetch basic SAE data.
            $saeSubject = $saeSubjectRepo->findById($sae_id);
            if (!$saeSubject) {
                throw new Exception("SAE introuvable.");
            }
            $data['subject'] = $saeSubject;

            // Reconstruct groups structure expected by view/logic.
            $groups = $saeGroupRepo->findBySaeSubjectId($sae_id);
            $formattedGroups = [];
            foreach ($groups as $group) {
                $students = $saeGroupRepo->getStudentsInGroup((int) $group->getSaeGroupId());
                $formattedGroups[] = ['group' => $group, 'students' => $students];
            }
            $data['groups'] = $formattedGroups;
            $allGroups = $formattedGroups;

            if ($this->user->isStudent()) {
                $studentRepo = new PdoStudentRepository();
                $canAccess = $studentRepo->canAccessSAE($userId, $sae_id);
                $currentGroupId = $participatedInRepo->getStudentGroupId($userId, $sae_id);
            } elseif ($this->user->isProfessor()) {
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
                $repository = new PdoToDoListRepository();
                $getTasksUseCase = new GetTasksUseCase($repository);
                $tasks = $getTasksUseCase->execute($currentGroupId);
            }

            $data['current_group_id'] = $currentGroupId;
            $data['tasks'] = $tasks;
            $data['all_groups'] = $allGroups;

            // Create and render the SAE page view.
            $view = new ToDoListView(
                $data['subject'],
                $data['current_group_id'],
                $data['tasks'],
                $data['all_groups'],
                $this->user
            );
            $view->render();
            exit();
        } catch (ExceptionAccessDenied $e) {
            SessionService::setFlash('errors', $e->getMessage());
            header('Location: /dashboard');
            exit();
        } catch (Exception $e) { // Catch general exception for SAE not found.
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
