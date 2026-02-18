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
use Models\UseCase\SAE\GetCompleteSAEDataUseCase;
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

            $useCase = new GetCompleteSAEDataUseCase(
                $subjectRepo,
                $groupRepo,
                $participatedInRepo,
                $studentRepo,
                $professorRepo,
                $clientRepo
            );

            $saeData = $useCase->execute($sae_id, $this->user);

            if (!$saeData) {
                throw new ExceptionAccessDenied("Accès refusé ou SAE introuvable.");
            }

            $this->verifyOwnership($saeData); // Verify ownership of the SAE.

            // Prepare data for view.
            $availableStudents = $this->getAvailableStudents($studentRepo, $sae_id);
            $profsAvailable = $this->getAvailableProfessors($professorRepo);

            $view = new ManageGroupsView([
                'user' => $this->user,
                'sae' => $saeData,
                'available_students' => $availableStudents,
                'all_professors' => $profsAvailable,
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
     * Verifies if the user has the permission to manage the SAE.
     *
     * @param array<mixed> $saeData The SAE data.
     *
     * @return void
     * @throws ExceptionAccessDenied If the user does not have permission to manage the SAE.
     */
    private function verifyOwnership(array $saeData): void
    {
        $responsibleProfId = $saeData['responsible_professor']['user_id'] ?? null;
        if ($this->user->getUserId() !== (int)$responsibleProfId) {
            throw new ExceptionAccessDenied("Vous n'avez pas la permission de gérer les groupes.");
        }
    }

    /**
     * Retrieves the list of students not in the SAE.
     *
     * @param PdoStudentRepository $repo  The student repository.
     * @param integer              $saeId The SAE ID.
     *
     * @return array<mixed> The list of students not in the SAE.
     */
    private function getAvailableStudents(PdoStudentRepository $repo, int $saeId): array
    {
        $students = $repo->findStudentsNotInSAE($saeId);
        return array_map(fn ($s) => $s->toArray(), $students);
    }

    /**
     * Retrieves the list of professors available to manage the SAE.
     *
     * @param PdoProfessorRepository $repo The professor repository.
     *
     * @return array<mixed> The list of professors available to manage the SAE.
     */
    private function getAvailableProfessors(PdoProfessorRepository $repo): array
    {
        if ($this->user instanceof Professor) {
            $profs = $repo->findAll();
            return array_map(fn ($p) => $p->toArray(), $profs);
        }
        return [];
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
