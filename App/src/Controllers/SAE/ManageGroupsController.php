<?php

namespace Controllers\SAE;

use Controllers\BaseController;
use Core\includes\exception\SAE\ExceptionAccessDenied;
use Core\Utilis\SessionService;
use Models\Entity\User\Professor;
use Models\Repository\SAE\PdoParticipatedInRepository;
use Models\Repository\SAE\PdoSAEGroupRepository;
use Models\Repository\SAE\PdoSAESubjectRepository;
use Models\Repository\User\PdoClientRepository;
use Models\Repository\User\PdoProfessorRepository;
use Models\Repository\User\PdoStudentRepository;
use Models\UseCase\SAE\GetManageGroupsDataUseCase;
use Override;
use Views\SAE\ManageGroupsView;

/**
 * Controller to display the group management page.
 *
 * @category   Controllers
 * @package    Src
 * @subpackage Controllers/SAE
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class ManageGroupsController extends BaseController
{
    /**
     * Controls the rendering of the group management page.
     *
     * @return void
     * @throws ExceptionAccessDenied If the user does not have permission to manage the SAE.
     */
    #[Override]
    public function control(): void
    {
        $this->ensureProfessor();

        $sae_id = $this->extractSaeId();
        if (!$sae_id) {
            header('Location: /dashboard');
            exit;
        }

        try {
            $subjectRepo = new PdoSAESubjectRepository();
            $groupRepo = new PdoSAEGroupRepository();
            $participatedInRepo = new PdoParticipatedInRepository();
            $studentRepo = new PdoStudentRepository();
            $professorRepo = new PdoProfessorRepository();
            $clientRepo = new PdoClientRepository();

            $useCase = new GetManageGroupsDataUseCase(
                $subjectRepo,
                $groupRepo,
                $participatedInRepo,
                $studentRepo,
                $professorRepo,
                $clientRepo
            );

            $data = $useCase->execute($sae_id, $this->user);

            $view = new ManageGroupsView([
                'user' => $this->user,
                'sae' => $data['sae'],
                'available_students' => $data['available_students'],
                'all_professors' => $data['all_professors'],
            ]);
            $view->render();
        } catch (ExceptionAccessDenied $e) {
            SessionService::setFlash('errors', $e->getMessage());
            header('Location: /sae/' . $sae_id);
            exit;
        }
    }

    /**
     * Extracts the SAE ID from the request URI.
     *
     * @return integer|null The extracted SAE ID, or null if not found.
     */
    private function extractSaeId(): ?int
    {
        $path = (string) (parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '');
        if (preg_match('/^\/sae\/(\d+)\/groups$/', $path, $matches)) {
            return intval($matches[1]);
        }
        return null;
    }



    /**
     * Determines if this controller supports the given path and method.
     *
     * @param string $path   The requested path.
     * @param string $method The HTTP method.
     *
     * @return boolean
     */
    #[Override]
    public static function support(string $path, string $method): bool
    {
        return preg_match('/^\/sae\/\d+\/groups$/', $path) && $method === "GET";
    }
}
