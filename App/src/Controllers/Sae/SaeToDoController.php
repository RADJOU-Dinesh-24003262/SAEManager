<?php

namespace Controllers\Sae;

use Models\Entity\ToDoList\ToDoList;
use Models\Repository\ToDoList\PdoToDoListRepository;
use Models\UseCase\ToDoList\GetToDoListContextUseCase;
use Controllers\BaseController;
use Core\Utils\SessionService;
use Core\Includes\Exception\SAE\ExceptionAccessDenied;
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
class SaeToDoController extends BaseController
{
    /**
     * Controls the rendering of the To-Do List view.
     *
     * @param integer $saeId The SAE ID.
     *
     * @return void
     * @throws Exception If the SAE doesn't exist.
     */
    public function control(int $saeId = 0): void
    {
        $this->ensureAuthenticated();

        try {
            $saeSubjectRepo = new PdoSAESubjectRepository();
            $saeGroupRepo = new PdoSAEGroupRepository();
            $participatedInRepo = new PdoParticipatedInRepository();


            $useCase = new GetToDoListContextUseCase(
                $saeSubjectRepo,
                $saeGroupRepo,
                $participatedInRepo,
                new PdoStudentRepository(),
                new PdoToDoListRepository()
            );

            $requestedGroupId = isset($_GET['group_id']) ? intval($_GET['group_id']) : null;
            $data = $useCase->execute($saeId, $this->user, $requestedGroupId);

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
