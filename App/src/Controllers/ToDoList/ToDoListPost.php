<?php

namespace Controllers\ToDoList;

use App\Models\ToDoList\ToDoList;
use Controllers\BaseController;
use Core\includes\exception\ExceptionValidation\ExceptionValidationEmpty;
use Core\includes\exception\SAE\ExceptionAccessDenied;
use Core\Utilis\Logger;
use Core\Utilis\SessionService;
use Exception;
use Models\SAE\SAE;
use Models\User\User;
use Override;
use Validator\ToDoListValidator;

/**
 * Controller for handling To-Do List POST actions (AJAX).
 *
 * Handles adding, updating (checking), and deleting tasks.
 *
 * @category Controller
 * @package Src
 * @subpackage Controllers/ToDoList
 *
 * @author Alexandre Benhafessa <alexandre.benhafessa@etu.univ-amu.fr>
 * @author François Dargentolle <francois.dargentolle@etu.univ-amu.fr>
 * @author William Edelstein <william.edelstein@etu.univ-amu.fr>
 * @author Nathan Griguer <nathan.griguer@etu.univ-amu.fr>
 * @author Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license MIT License https://opensource.org/licenses/MIT
 * @link https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class ToDoListPost extends BaseController
{
    /**
     * Main control method.
     * Dispatches to specific handlers based on the action.
     *
     * @return void
     * @throws ExceptionAccessDenied If the user access to SAE is denied.
     */
    #[Override]
    public function control(): void
    {
        $this->ensureAuthenticated();

        header('Content-Type: application/json');

        // Verify CSRF Token
        $csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!SessionService::verifyCsrfToken($csrfToken)) {
             Logger::log('CSRF_FAIL', 'Tentative action TODO avec token invalide.', $this->user->getUserId(), 'WARNING');
             $this->sendError("Session invalide (CSRF).", 403);
        }

        $user = $this->user;
        $validator = new ToDoListValidator();

        try {
            $params = $this->parseUri();
            $saeId = $params['sae_id'];
            $action = $params['action'];
            $todoId = $params['todo_id'];

            $json = file_get_contents('php://input');
            if ($json === false) {
                $json = '{}';
            }
            $input = json_decode($json, true) ?? [];
            if (!is_array($input)) {
                $input = [];
            }

            if (!$this->user->canAccessSAE($saeId)) {
                throw new ExceptionAccessDenied("Vous n'avez pas accès à cette SAE.");
            }

            $targetGroupId = $this->determineTargetGroupId($user, $saeId, $input);

            // Check permissions based on the group.
            $this->checkGroupAccess($user, $saeId, $targetGroupId);

            switch ($action) {
                case 'add':
                    // Map description to tododesc for validation
                    $dataToValidate = ['tododesc' => $input['description'] ?? ''];
                    $validator->validate($dataToValidate);
                    
                    $this->handleAdd($targetGroupId, $input);
                    Logger::log('TODO_ADD', "Tâche ajoutée par utilisateur {$user->getUserId()} dans SAE $saeId", $user->getUserId());
                    break;
                case 'update':
                    $this->handleUpdate($todoId, $input);
                    Logger::log('TODO_UPDATE', "Tâche $todoId mise à jour par utilisateur {$user->getUserId()}", $user->getUserId());
                    break;
                case 'delete':
                    $this->handleDelete($todoId);
                    Logger::log('TODO_DELETE', "Tâche $todoId supprimée par utilisateur {$user->getUserId()}", $user->getUserId());
                    break;
                default:
                    $this->sendError("Action non reconnue.", 400);
            }
        } catch (ExceptionAccessDenied $e) {
            Logger::log('TODO_ACCESS_DENIED', $e->getMessage(), $user->getUserId(), 'WARNING');
            $this->sendError($e->getMessage(), ($e->getCode() ?: 403));
        } catch (ExceptionValidationEmpty $e) {
            Logger::log('TODO_VALIDATION_ERROR', "Description vide", $user->getUserId(), 'INFO');
            $this->sendError($e->getMessage(), 400);
        } catch (Exception $e) {
            Logger::log('TODO_ERROR', "Erreur interne: " . $e->getMessage(), $user->getUserId(), 'ERROR');
            $this->sendError("Erreur interne.", 500);
        }
    }

    /**
     * Parses the request URI to extract SAE ID, action, and Task ID.
     *
     * @return array{sae_id: int, action: string, todo_id: int}
     */
    private function parseUri(): array
    {
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        if (!is_string($path)) {
            $path = '';
        }
        $parts = explode('/', $path);

        return [
            'sae_id' => intval($parts[2] ?? 0),
            'action' => $parts[4] ?? '',
            'todo_id' => intval($parts[5] ?? 0)
        ];
    }

    /**
     * Determines the group ID to operate on.
     *
     * @param User                 $user  The user.
     * @param integer              $saeId The SAE ID.
     * @param array<string, mixed> $input The input data.
     * @return integer
     * @throws ExceptionAccessDenied If access to the SAE is denied.
     */
    private function determineTargetGroupId(User $user, int $saeId, array $input): int
    {
        $saeManager = SAE::getInstance();
        $saeData = $saeManager->getCompleteSAEData($saeId, $user);

        if (!$saeData) {
            throw new ExceptionAccessDenied("Accès non autorisé à cette SAE.", 403);
        }

        if ($user->isStudent()) {
            if (empty($saeData['groups'])) {
                throw new ExceptionAccessDenied("Vous n'êtes assigné à aucun groupe.", 403);
            }
            $groupData = reset($saeData['groups']);
            return (int) $groupData['group']->getSaeGroupId();
        }

        // Professors can view but NOT add/modify via this controller.
        throw new ExceptionAccessDenied("Seuls les étudiants peuvent gérer les tâches.", 403);
    }

    /**
     * Checks if the user has access to the specific group.
     *
     * @param User    $user    The user to check if has access to the group.
     * @param integer $saeId   The SAE ID to check.
     * @param integer $groupId The group ID to check.
     * @throws ExceptionAccessDenied If the user access to the group is denied.
     * @return void
     */
    private function checkGroupAccess(User $user, int $saeId, int $groupId): void
    {
        $saeManager = SAE::getInstance();
        $saeData = $saeManager->getCompleteSAEData($saeId, $user);

        $hasAccess = false;
        if (isset($saeData['groups'])) {
            foreach ($saeData['groups'] as $groupData) {
                if ($groupData['group']->getSaeGroupId() == $groupId) {
                    $hasAccess = true;
                    break;
                }
            }
        }

        if (!$hasAccess) {
            throw new ExceptionAccessDenied("Vous n'avez pas accès à ce groupe.", 403);
        }
    }

    /**
     * Handles adding a new task to the To-Do list.
     *
     * @param integer              $groupId The ID of the group to which the task will be added.
     * @param array<string, mixed> $input   An associative array containing 'description' (string)
     *                                      and 'priority' (int) for the new task.
     * @return void This method does not return any value, it sends a JSON response and exits.
     * @throws ExceptionValidationEmpty If the task description is empty.
     */
    private function handleAdd(int $groupId, array $input): void
    {
        $description = isset($input['description']) ? trim((string)$input['description']) : '';
        $priority = isset($input['priority']) ? intval($input['priority']) : 2;


        $newId = ToDoList::createTask($groupId, $description, $priority);

        if ($newId) {
            echo json_encode([
                'success' => true,
                'todo_id' => $newId,
                'description' => $description,
                'priority' => $priority
            ]);
        } else {
            throw new Exception("Erreur lors de la création.");
        }
    }

    /**
     * Handles updating a task (checked status AND/OR priority).
     *
     * @param integer              $todoId The task ID.
     * @param array<string, mixed> $input  JSON input data.
     * @return void
     */
    private function handleUpdate(int $todoId, array $input): void
    {
        if (!$todoId) {
            $this->sendError("ID de tâche manquant.", 400);
            return;
        }

        $success = true;
        $updated = false;

        // Update Checked Status if provided.
        if (isset($input['checked'])) {
            $checked = (bool)$input['checked'];
            if (!ToDoList::updateCheckedStatus($todoId, $checked)) {
                $success = false;
            }
            $updated = true;
        }

        // Update Priority if provided.
        if (isset($input['priority'])) {
            $priority = intval($input['priority']);
            if ($priority >= 1 && $priority <= 3) {
                if (!ToDoList::updatePriority($todoId, $priority)) {
                    $success = false;
                }
                $updated = true;
            } else {
                $this->sendError("Priorité invalide (doit être entre 1 et 3).", 400);
                return;
            }
        }

        if (!$updated) {
            $this->sendError("Aucune donnée à mettre à jour.", 400);
            return;
        }

        if ($success) {
            echo json_encode(['success' => true]);
        } else {
            throw new Exception("Erreur lors de la mise à jour.");
        }
    }

    /**
     * Handles deleting a task.
     *
     * @param integer $todoId The task ID.
     * @return void
     */
    private function handleDelete(int $todoId): void
    {
        if (!$todoId) {
            $this->sendError("ID de tâche manquant.", 400);
            return;
        }

        if (ToDoList::deleteTask($todoId)) {
            echo json_encode(['success' => true]);
        } else {
            throw new Exception("Erreur lors de la suppression.");
        }
    }

    /**
     * Sends a JSON error response.
     *
     * @param string  $message The error message to be sent in the JSON response.
     * @param integer $code    The HTTP status code to be sent with the response.
     *
     * @return void
     */
    private function sendError(string $message, int $code): void
    {
        http_response_code($code);
        echo json_encode(['success' => false, 'message' => $message]);
        exit();
    }

    /**
     * Determines if the controller supports the given URL path and HTTP method.
     *
     * @param string $path   The URL path to check for support.
     * @param string $method The HTTP method (e.g., "GET", "POST") to check for support.
     *
     * @return boolean Returns true if the controller supports the path and method, false otherwise.
     */
    #[Override]
    public static function support(string $path, string $method): bool
    {
        return preg_match(
            '/^\/sae\/\d+\/to-do\/(add|update\/\d+|delete\/\d+)$/',
            $path
        ) && $method === "POST";
    }
}
