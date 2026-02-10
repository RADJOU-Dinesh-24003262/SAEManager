<?php

namespace App\GUI\Controllers\SAE;

use App\GUI\Controllers\BaseController;
use App\Infrastructure\Service\SessionService;
use App\Application\SAE\CreateSaeGroupUseCase;
use App\Application\SAE\DeleteSaeGroupUseCase;
use App\Application\SAE\AssignStudentToGroupUseCase;
use App\Application\SAE\RemoveStudentFromGroupUseCase;
use App\Infrastructure\Persistence\Pdo\PdoSaeRepository;
use App\Infrastructure\Persistence\Pdo\PdoSaeGroupRepository;
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
     * @return void
     * @throws \Exception If an unknown action is encountered or an error occurs during processing.
     */
    #[Override]
    public function control(): void
    {
        $this->ensureProfessor();

        $path = (string) (parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '');

        $saeId = 0;
        $action = '';

        // Extract SAE ID and Action from URL: /sae/{id}/groups/{action}.
        if (preg_match('/^\/sae\/(\d+)\/groups\/(.+)$/', $path, $matches)) {
            $saeId = intval($matches[1]);
            $action = $matches[2];
        }

        try {
            match ($action) {
                'create' => $this->createGroup($saeId),
                'delete' => $this->deleteGroup($saeId),
                'add-student' => $this->addStudent($saeId),
                'remove-student' => $this->removeStudent($saeId),
                default => throw new Exception('Action non reconnue')
            };
        } catch (Exception $e) {
            $this->redirectWithError($saeId, $e->getMessage());
        }
    }

    /**
     * Creates a new group for a SAE.
     *
     * @param integer $saeId The ID of the SAE.
     * @return void
     * @throws \Exception If an unexpected error occurs during group creation.
     */
    private function createGroup(int $saeId): void
    {
        $professorId = filter_input(INPUT_POST, 'professor_id', FILTER_VALIDATE_INT);

        // If professorId is false (invalid) or null (not set), treat it as null (no professor).
        if ($professorId == false) {
            $professorId = null;
        }

        $useCase = new CreateSaeGroupUseCase(new PdoSaeGroupRepository(), new PdoSaeRepository());
        $useCase->execute($this->user, $saeId, $professorId);

        $this->redirectWithSuccess($saeId, 'Groupe créé avec succès.');
    }

    /**
     * Deletes a group.
     *
     * @param integer $saeId The ID of the SAE.
     * @return void
     * @throws \Exception If the group ID is missing.
     * @throws \Exception If an unexpected error occurs during group deletion.
     */
    private function deleteGroup(int $saeId): void
    {
        $groupId = filter_input(INPUT_POST, 'group_id', FILTER_VALIDATE_INT);
        if (!$groupId) {
            throw new Exception("ID du groupe manquant");
        }

        $useCase = new DeleteSaeGroupUseCase(new PdoSaeGroupRepository(), new PdoSaeRepository());
        $useCase->execute($this->user, $groupId);

        $this->redirectWithSuccess($saeId, 'Groupe supprimé.');
    }

    /**
     * Adds a student to a group.
     *
     * @param integer $saeId The ID of the SAE.
     * @return void
     * @throws \Exception If group ID or student ID is missing.
     * @throws \Exception If an unexpected error occurs during student assignment.
     */
    private function addStudent(int $saeId): void
    {
        $groupId = filter_input(INPUT_POST, 'group_id', FILTER_VALIDATE_INT);
        $studentId = filter_input(INPUT_POST, 'student_id', FILTER_VALIDATE_INT);
        if (!$groupId || !$studentId) {
            throw new Exception("Données manquantes");
        }

        $useCase = new AssignStudentToGroupUseCase(new PdoSaeGroupRepository(), new PdoSaeRepository());
        $useCase->execute($this->user, $studentId, $groupId);

        $this->redirectWithSuccess($saeId, 'Étudiant ajouté au groupe.');
    }

    /**
     * Removes a student from a group.
     *
     * @param integer $saeId The ID of the SAE.
     * @return void
     * @throws \Exception If group ID or student ID is missing.
     * @throws \Exception If an unexpected error occurs during student removal.
     */
    private function removeStudent(int $saeId): void
    {
        $groupId = filter_input(INPUT_POST, 'group_id', FILTER_VALIDATE_INT);
        $studentId = filter_input(INPUT_POST, 'student_id', FILTER_VALIDATE_INT);
        if (!$groupId || !$studentId) {
            throw new Exception("Données manquantes");
        }

        $useCase = new RemoveStudentFromGroupUseCase(new PdoSaeGroupRepository(), new PdoSaeRepository());
        $useCase->execute($this->user, $studentId, $groupId, $saeId);

        $this->redirectWithSuccess($saeId, 'Étudiant retiré du groupe.');
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