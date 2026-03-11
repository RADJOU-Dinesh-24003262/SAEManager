
<?php

namespace Controllers\SAE;

use Controllers\BaseController;
use Core\Utilis\Logger;
use Core\Utilis\SessionService;
use Models\Repository\SAE\PdoParticipatedInRepository;
use Models\Repository\SAE\PdoSAEGroupRepository;
use Models\Repository\SAE\PdoSAESubjectRepository;
use Models\UseCase\SAE\AssignStudentToGroupUseCase;
use Models\UseCase\SAE\CreateSAEGroupUseCase;
use Models\UseCase\SAE\DeleteSAEGroupUseCase;
use Models\UseCase\SAE\RemoveStudentFromGroupUseCase;
use Override;
use Exception;

/**
 * Controller to handle POST actions for group management.
 *
 * @category   Controllers
 * @package    Src
 * @subpackage Controllers/SAE
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class ManageGroupsPostController extends BaseController
{
    /**
     * Controls the processing of group management actions.
     *
     * @param integer $saeId  The SAE ID.
     * @param string  $action The action to perform.
     *
     * @return void
     * @throws \Exception If an unknown action is encountered or an error occurs during processing.
     */
    public function control(int $saeId = 0, string $action = ''): void
    {
        $this->ensureProfessor();

        try {
            match ($action) {
                'create' => $this->createGroup($saeId),
                'delete' => $this->deleteGroup($saeId),
                'add-student' => $this->addStudent($saeId),
                'remove-student' => $this->removeStudent($saeId),
                default => throw new Exception("Action inconnue: $action")
            };
        } catch (Exception $e) {
            $this->redirectWithError($saeId, $e->getMessage());
        }
    }

    /**
     * Creates a new group for a SAE.
     *
     * @param integer $saeId The ID of the SAE.
     *
     * @return void
     * @throws Exception If an unexpected error occurs during group creation.
     */
    private function createGroup(int $saeId): void
    {
        $professorId = filter_input(INPUT_POST, 'professor_id', FILTER_VALIDATE_INT);

        // If professorId is false (invalid) or null (not set), treat it as null (no professor).
        if ($professorId == false) {
            $professorId = null;
        }

        $useCase = new CreateSAEGroupUseCase(
            new PdoSAEGroupRepository(),
            new PdoSAESubjectRepository()
        );
        $useCase->execute($this->user, $saeId, $professorId);

        $this->redirectWithSuccess($saeId, 'Groupe créé avec succès.');
        Logger::log('SAE_Group_Creation_failure', "Success de la création d'un groupe de SAE" . $this->user->getEmail(), $this->user->getUserId());

    }

    /**
     * Deletes a group.
     *
     * @param integer $saeId The ID of the SAE.
     * @return void
     * @throws Exception If the group ID is missing.
     * @throws Exception If an unexpected error occurs during group deletion.
     */
    private function deleteGroup(int $saeId): void
    {
        $groupId = filter_input(INPUT_POST, 'group_id', FILTER_VALIDATE_INT);
        if (!$groupId) {
            throw new Exception("ID du groupe manquant");
        }

        $useCase = new DeleteSAEGroupUseCase(
            new PdoSAEGroupRepository(),
            new PdoSAESubjectRepository()
        );
        $useCase->execute($this->user, $groupId);

        $this->redirectWithSuccess($saeId, 'Groupe supprimé.');
        Logger::log('SAE_Group_Deletion_failure', "Success de la suppression d'un groupe de SAE" . $this->user->getEmail(), $this->user->getUserId());

    }

    /**
     * Adds a student to a group.
     *
     * @param integer $saeId The ID of the SAE.
     * @return void
     * @throws Exception If group ID or student ID is missing.
     * @throws Exception If an unexpected error occurs during student assignment.
     */
    private function addStudent(int $saeId): void
    {
        $groupId = filter_input(INPUT_POST, 'group_id', FILTER_VALIDATE_INT);
        $studentId = filter_input(INPUT_POST, 'student_id', FILTER_VALIDATE_INT);
        if (!$groupId || !$studentId) {
            throw new Exception("Données manquantes");
        }

        $useCase = new AssignStudentToGroupUseCase(
            new PdoSAEGroupRepository(),
            new PdoParticipatedInRepository(),
            new PdoSAESubjectRepository()
        );
        $useCase->execute($this->user, $studentId, $groupId);

        $this->redirectWithSuccess($saeId, 'Étudiant ajouté au groupe.');
        Logger::log('SAE_Group_Student_Added', "Success de l'ajout d'un utilisateur au groupe de SAE" . $this->user->getEmail(), $this->user->getUserId());

    }

    /**
     * Removes a student from a group.
     *
     * @param integer $saeId The ID of the SAE.
     * @return void
     * @throws Exception If group ID or student ID is missing.
     * @throws Exception If an unexpected error occurs during student removal.
     */
    private function removeStudent(int $saeId): void
    {
        $groupId = filter_input(INPUT_POST, 'group_id', FILTER_VALIDATE_INT);
        $studentId = filter_input(INPUT_POST, 'student_id', FILTER_VALIDATE_INT);
        if (!$groupId || !$studentId) {
            throw new Exception("Données manquantes");
        }

        $useCase = new RemoveStudentFromGroupUseCase(
            new PdoSAEGroupRepository(),
            new PdoParticipatedInRepository(),
            new PdoSAESubjectRepository()
        );
        $useCase->execute($this->user, $studentId, $groupId);

        $this->redirectWithSuccess($saeId, 'Étudiant retiré du groupe.');
        Logger::log('SAE_Group_Student_Removed', "Success de la suppression d'un utilisateur au groupe de SAE" . $this->user->getEmail(), $this->user->getUserId());

    }

    /**
     * Redirects with a success message.
     *
     * @param integer $saeId The ID of the SAE.
     * @param string  $msg   The success message.
     * @return void
     */
    private function redirectWithSuccess(int $saeId, string $msg): void
    {
        SessionService::setFlash('success', $msg);
        header('Location: /sae/' . $saeId . '/groups');
        exit;
    }

    /**
     * Redirects with an error message.
     *
     * @param integer $saeId The ID of the SAE.
     * @param string  $msg   The error message.
     * @return void
     */
    private function redirectWithError(int $saeId, string $msg): void
    {
        SessionService::setFlash('errors', $msg);
        header('Location: /sae/' . $saeId . '/groups');
        exit;
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
        return $method === 'POST'
            && preg_match(
                '/^\/sae\/\d+\/groups\/(create|delete|add-student|remove-student)$/',
                $path
            );
    }
}