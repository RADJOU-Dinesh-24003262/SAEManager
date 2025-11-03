<?php

namespace Controllers\ToDoList;

use App\Models\ToDoList\ToDoList;
use Core\AbstractView;
use Core\ControllerInterface;
use Core\includes\exception\ExceptionValidation\ExceptionValidation;
//use Core\includes\exception\ExceptionValidation\ExceptionValidationEmpty;
use Core\includes\exception\ExceptionValidation\ExceptionValidationEmptys;
use Core\Utilis\SessionService;
use Validator\ToDoListValidator;
use Views\ToDoList\ToDoListView;

/**
 * This class controls the todolist process (post).

 * @category Controller

 * @package Src

 * @subpackage Controllers\ToDoList

 * @author  Alexandre Benhafessa <alexandre.benhafessa@etu.univ-amu.fr>,
 * @author  François Dargentolle <francois.dargentolle@etu.univ-amu.fr>,
 * @author  William Edelstein <william.edelstein@etu.univ-amu.fr>,
 * @author  Nathan Griguer <nathan.griguer@etu.univ-amu.fr>,
 * @author  Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>

 * @license MIT License https://opensource.org/licenses/MIT

 * @link https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class ToDoListPost implements ControllerInterface
{
    /**
     * Principal manager of the controller
     *
     * @return void
     *
     * @throws \Exception For any other unexpected errors during the save of the task process.
     */
    public function control(): void
    {
        // Validate the data
        $validator = new ToDoListValidator();

        try {
            $data = $validator->escape($_POST);
            $validator->validate($data);

            // Create the ToDoList
            $todolist = ToDoList::create($data);

            // Save the ToDoList
            if ($todolist->save()) {
                error_log("Nouvel tâche enregistré : " . $todolist->getToDoId());
                $view = new ToDoListView();
                $view->render();

                return;
            } else {
                error_log("Erreur tâche enregistré : " . $todolist->getToDoId());
                throw new \Exception("Erreur lors de la sauvegarde");
            }
        } catch (ExceptionValidation | ExceptionValidationEmpty $e) {
            $errors = [];
            foreach ($e->getErrors() as $error) {
                $errors[] = $error->getMessage();
            }
        } catch (\PDOException $e) {
            error_log("Erreur récupération données utilisateur: " . $e->getMessage());
            SessionService::setFlash('errors', ['general' => 'Une erreur est survenu, réessayez plus tard']);
        } catch (\Exception $e) {
            SessionService::setFlash('errors', ['general' => 'Erreur lors de l\'inscription: ' . $e->getMessage()]);
        }
        $view = new ToDoListView();
        $view->render();
    }

    /**
     * Determines whether this controller supports the given request.
     *
     * @param string $path   The requested URI path.
     * @param string $method The HTTP method used in the request.
     *
     * @return boolean True if the path is "/to-do-list" and the method is POST.
     */
    public static function support(string $path, string $method): bool
    {
        return $path === "/to-do-list" && $method === "POST";
    }
}
