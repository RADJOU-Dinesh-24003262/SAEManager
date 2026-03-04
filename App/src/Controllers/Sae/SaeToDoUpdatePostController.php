<?php

namespace Controllers\Sae;

use Models\Repository\SAE\PdoParticipatedInRepository;
use Models\Repository\SAE\PdoSAEGroupRepository;
use Models\Repository\SAE\PdoSAESubjectRepository;
use Models\Repository\ToDoList\PdoToDoListRepository;
use Models\Repository\User\PdoClientRepository;
use Models\Repository\User\PdoProfessorRepository;
use Models\Repository\User\PdoStudentRepository;
use Models\UseCase\ToDoList\ValidateToDoListModifyAccessUseCase;
use Models\UseCase\ToDoList\UpdateTaskUseCase;
use Controllers\BaseController;
use Core\includes\exception\ExceptionValidation\ExceptionValidationToDoList;
use Core\includes\exception\SAE\ExceptionAccessDenied;
use Core\Utilis\Logger;
use Core\Utilis\SessionService;
use Exception;
use Override;
use Validator\ToDoListValidator;

/**
 * Controller for handling To-Do List Update POST actions (AJAX).
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
class SaeToDoUpdatePostController extends BaseController
{
    /**
     * Main control method for updating a task.
     *
     * @param integer $saeId  The SAE ID.
     * @param integer $todoId The To-Do ID.
     * @return void
     */
    public function control(int $saeId = 0, int $todoId = 0): void
    {
        $this->ensureAuthenticated();

        $repository = new PdoToDoListRepository();

        header('Content-Type: application/json');

        $this->checkCsrfAjax('TODO_UPDATE');

        $user = $this->user;
        $validator = new ToDoListValidator();

        try {
            $json = file_get_contents('php://input');
            $input = json_decode($json !== false ? $json : '{}', true) ?? [];
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

            $validateAccessUseCase->execute($user, $saeId);

            $input = $validator->escape($input);
            $validator->validate($input);

            $updateTaskUseCase = new UpdateTaskUseCase($repository);
            $updateTaskUseCase->execute($todoId, $input);

            Logger::log('TODO_UPDATE', "Task $todoId updated by user {$user->getUserId()}", $user->getUserId());

            echo json_encode(['success' => true]);
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
     * Sends an error response.
     *
     * @param string  $message The error message.
     * @param integer $code    The HTTP status code.
     * @return void
     */
    private function sendError(string $message, int $code): void
    {
        http_response_code($code);
        echo json_encode(['success' => false, 'message' => $message]);
        exit();
    }

    /**
     * Checks if the request path and method are supported.
     *
     * @param string $path   The request path.
     * @param string $method The request method.
     * @return boolean True if the request is supported, false otherwise.
     */
    #[Override]
    public static function support(string $path, string $method): bool
    {
        return preg_match(
            '/^\/sae\/\d+\/to-do\/update\/\d+$/',
            $path
        ) && $method === "POST";
    }
}
