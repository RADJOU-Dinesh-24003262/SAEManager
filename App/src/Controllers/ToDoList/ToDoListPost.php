<?php

namespace Controllers\ToDoList;

use Models\Entity\ToDoItem\ToDoItem;
use Models\Repository\SAE\PdoParticipatedInRepository;
use Models\Repository\SAE\PdoSAEGroupRepository;
use Models\Repository\SAE\PdoSAESubjectRepository;
use Models\Repository\ToDoList\PdoToDoListRepository;
use Models\Repository\User\PdoClientRepository;
use Models\Repository\User\PdoProfessorRepository;
use Models\Repository\User\PdoStudentRepository;
use Models\UseCase\SAE\GetCompleteSAEDataUseCase;
use Models\UseCase\ToDoList\ValidateToDoListModifyAccessUseCase;
use Models\UseCase\ToDoList\CreateTaskUseCase;
use Models\UseCase\ToDoList\DeleteTaskUseCase;
use Models\UseCase\ToDoList\UpdateTaskUseCase;
use Controllers\BaseController;
use Core\includes\exception\ExceptionValidation\ExceptionValidationToDoList;
use Core\includes\exception\SAE\ExceptionAccessDenied;
use Core\Utilis\Logger;
use Core\Utilis\SessionService;
use Exception;
use Models\Entity\User\User;
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
     * @var PdoToDoListRepository
     */
    private PdoToDoListRepository $repository;

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

        $this->repository = new PdoToDoListRepository();

        header('Content-Type: application/json');

        // Verify CSRF Token.
        $csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!SessionService::verifyCsrfToken($csrfToken)) {
            Logger::log('CSRF_FAIL', 'Invalid CSRF token for TODO action.', $this->user->getUserId(), 'WARNING');
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

            $validateAccessUseCase = new ValidateToDoListModifyAccessUseCase(
                new PdoSAESubjectRepository(),
                new PdoSAEGroupRepository(),
                new PdoParticipatedInRepository(),
                new PdoStudentRepository(),
                new PdoProfessorRepository(),
                new PdoClientRepository()
            );

            $targetGroupId = $validateAccessUseCase->execute($user, $saeId);

            $userId = $user->getUserId();
            switch ($action) {
                case 'add':
                    // Ensure description is validated (required for add).
                    if (!isset($input['description'])) {
                        $input['description'] = '';
                    }
                    $validator->validate($input);

                    $this->handleAdd($targetGroupId, $input);
                    Logger::log('TODO_ADD', "Task added by user {$userId} in SAE $saeId", $userId);
                    break;
                case 'update':
                    $validator->validate($input);
                    $this->handleUpdate($todoId, $input);
                    Logger::log('TODO_UPDATE', "Task $todoId updated by user {$userId}", $userId);
                    break;
                case 'delete':
                    $this->handleDelete($todoId);
                    Logger::log('TODO_DELETE', "Task $todoId deleted by user {$userId}", $userId);
                    break;
                default:
                    $this->sendError("Action non reconnue.", 400);
            }
        } catch (ExceptionAccessDenied $e) {
            Logger::log('TODO_ACCESS_DENIED', $e->getMessage(), $user->getUserId(), 'WARNING');
            $this->sendError($e->getMessage(), ($e->getCode() ?: 403));
        } catch (ExceptionValidationToDoList $e) {
            Logger::log('TODO_VALIDATION_ERROR', $e->getMessage(), $user->getUserId(), 'INFO');
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
     * Handles adding a new task to the To-Do list.
     *
     * @param integer              $groupId The ID of the group to which the task will be added.
     * @param array<string, string> $input   An associative array containing 'description' (string)
     *                                      and 'priority' (int) for the new task.
     * @return void This method does not return any value, it sends a JSON response and exits.
     * @throws Exception If there is an error during task creation.
     */
    private function handleAdd(int $groupId, array $input): void
    {
        $createTaskUseCase = new CreateTaskUseCase($this->repository);
        $task = $createTaskUseCase->execute($groupId, trim($input['description']), intval($input['priority']));

        echo json_encode([
            'success' => true,
            'todo_id' => $task->getTodoId(),
            'description' => $task->getTodoDesc(),
            'priority' => $task->getPriority()
        ]);
    }

    /**
     * Handles updating a task (checked status AND/OR priority).
     *
     * @param integer              $todoId The task ID.
     * @param array<string, mixed> $input  JSON input data.
     * @return void
     * @throws Exception If there is an error during the update.
     */
    private function handleUpdate(int $todoId, array $input): void
    {
        $updates = [];

        // Update Checked Status if provided.
        if (isset($input['checked'])) {
            $updates['checked'] = (bool)$input['checked'];
        }

        // Update Priority if provided.
        if (isset($input['priority'])) {
            $updates['priority'] = intval($input['priority']);
        }

        if (empty($updates)) {
            $this->sendError("Aucune donnée à mettre à jour.", 400);
            return;
        }

        $updateTaskUseCase = new UpdateTaskUseCase($this->repository);
        $updateTaskUseCase->execute($todoId, $updates);

        echo json_encode(['success' => true]);
    }

    /**
     * Handles deleting a task.
     *
     * @param integer $todoId The task ID.
     * @return void
     * @throws Exception If there is an error during deletion.
     */
    private function handleDelete(int $todoId): void
    {
        $deleteTaskUseCase = new DeleteTaskUseCase($this->repository);
        $deleteTaskUseCase->execute($todoId);

        echo json_encode(['success' => true]);
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