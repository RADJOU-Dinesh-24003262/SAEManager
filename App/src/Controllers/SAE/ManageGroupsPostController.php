<?php

namespace Controllers\SAE;

use Controllers\BaseController;
use Core\Utilis\SessionService;
use Models\SAE\SAE;
use Override;

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
     */
    #[Override]
    public function control(): void
    {
        $this->ensureProfessor();

        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Extract SAE ID and Action from URL: /sae/{id}/groups/{action}
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
                default => throw new \Exception('Action non reconnue')
            };
        } catch (\Exception $e) {
            $this->redirectWithError($saeId, $e->getMessage());
        }
    }

    private function createGroup(int $saeId): void
    {
        $professorId = filter_input(INPUT_POST, 'professor_id', FILTER_VALIDATE_INT);
        if (!$professorId) {
            throw new \Exception("ID du professeur manquant");
        }
        SAE::getInstance()->createGroup($this->user, $saeId, $professorId);
        $this->redirectWithSuccess($saeId, 'Groupe créé avec succès.');
    }

    private function deleteGroup(int $saeId): void
    {
        $groupId = filter_input(INPUT_POST, 'group_id', FILTER_VALIDATE_INT);
        if (!$groupId) {
             throw new \Exception("ID du groupe manquant");
        }
        SAE::getInstance()->deleteGroup($this->user, $groupId);
        $this->redirectWithSuccess($saeId, 'Groupe supprimé.');
    }

    private function addStudent(int $saeId): void
    {
        $groupId = filter_input(INPUT_POST, 'group_id', FILTER_VALIDATE_INT);
        $studentId = filter_input(INPUT_POST, 'student_id', FILTER_VALIDATE_INT);
        if (!$groupId || !$studentId) {
             throw new \Exception("Données manquantes");
        }
        SAE::getInstance()->assignStudentToGroup($this->user, $studentId, $groupId);
        $this->redirectWithSuccess($saeId, 'Étudiant ajouté au groupe.');
    }

    private function removeStudent(int $saeId): void
    {
        $groupId = filter_input(INPUT_POST, 'group_id', FILTER_VALIDATE_INT);
        $studentId = filter_input(INPUT_POST, 'student_id', FILTER_VALIDATE_INT);
        if (!$groupId || !$studentId) {
             throw new \Exception("Données manquantes");
        }
        SAE::getInstance()->removeStudentFromGroup($this->user, $studentId, $groupId);
        $this->redirectWithSuccess($saeId, 'Étudiant retiré du groupe.');
    }

    private function redirectWithSuccess(int $saeId, string $msg): void
    {
        SessionService::setFlash('success', $msg);
        header('Location: /sae/' . $saeId . '/groups');
        exit;
    }

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
