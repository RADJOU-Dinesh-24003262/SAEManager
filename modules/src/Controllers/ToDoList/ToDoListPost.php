<?php

namespace Controllers\ToDoList;

use Controllers\ControllerInterface;
use includes\exception\ExceptionValidationEmpty;
use includes\exception\ExceptionValidationEmptys;
use Models\ToDoList\ToDoList;
use Utilis\SessionService;
use Utilis\Validator\ToDoListValidator;
use Views\ToDoList\ToDoListView;
use includes\exception\ExceptionValidation;

class ToDoListPost implements ControllerInterface
{


    public function control(): void
    {
        $validator = new ToDoListValidator();

        try{
            $data = $validator->escape($_POST);
            $validator->validate($data);

            $todolist = ToDoList::create($data);

            if ($todolist->save()) {
                error_log("Nouvel tâche enregistré : " . $todolist.getToDoId());
                $view = new ToDoListView($todolist);
                $view->render();

                return;
            } else {
                error_log("Erreur tâche enregistré : " . $todolist.getToDoId());
                throw new \Exception("Erreur lors de la sauvegarde");
            }
        }
        catch (ExceptionValidation | ExceptionValidationEmpty $e){
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

    public static function support(string $path, string $method): bool
    {
        return $path === "/to-do-list" && $method === "POST";
    }
}