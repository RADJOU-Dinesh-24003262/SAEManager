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
use Models\UseCase\ToDoList\DeleteTaskUseCase;
use Controllers\BaseController;
use Core\Includes\Exception\ExceptionCsrf;
use Core\Includes\Exception\ExceptionValidation\ExceptionValidationToDoList;
use Core\Includes\Exception\SAE\ExceptionAccessDenied;
use Core\Utils\Logger;
use Core\Utils\SessionService;
use Exception;
use Override;

/**
 * Controller for handling To-Do List Delete POST actions (AJAX).
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
class SaeToDoDeletePostController extends BaseController
{
    /**
     * Main control method for deleting a task.
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

        $user = $this->user;

        try {
            $this->checkCsrfAjax('TODO_DELETE');
            $validateAccessUseCase = new ValidateToDoListModifyAccessUseCase(
                new PdoSAESubjectRepository(),
                new PdoSAEGroupRepository(),
                new PdoParticipatedInRepository(),
                new PdoStudentRepository(),
                new PdoProfessorRepository(),
                new PdoClientRepository()
            );

            $validateAccessUseCase->execute($user, $saeId);

            $deleteTaskUseCase = new DeleteTaskUseCase($repository);
            $deleteTaskUseCase->execute($todoId);

            Logger::log('TODO_DELETE', "Task $todoId deleted by user {$user->getUserId()}", $user->getUserId());

            echo json_encode(['success' => true]);
        } catch (ExceptionCsrf $e) {
            $this->sendError($e->getMessage(), 403);
        } catch (ExceptionAccessDenied $e) {
            Logger::log('TODO_ACCESS_DENIED', $e->getMessage(), $user->getUserId(), 'WARNING');
            $this->sendError($e->getMessage(), ($e->getCode() ?: 403));
        } catch (ExceptionValidationToDoList $e) {
            Logger::log('TODO_VALIDATION_ERROR', $e->getMessage(), $user->getUserId(), 'INFO');
            $this->sendError($e->getMessage(), 400);
        } catch (Exception $e) {
            Logger::log('TODO_ERROR', "Internal Error: " . $e->getMessage(), $user->getUserId(), 'ERROR');
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
     * Checks if the controller supports the given path and method.
     *
     * @param string $path   The path to check.
     * @param string $method The HTTP method to check.
     * @return boolean True if the controller supports the path and method, false otherwise.
     */
    #[Override]
    public static function support(string $path, string $method): bool
    {
        return preg_match(
            '/^\/sae\/\d+\/to-do\/delete\/\d+$/',
            $path
        ) && $method === "POST";
    }
}
