<?php
namespace Controllers\ToDoList;

use Controllers\ControllerInterface;
use Views\ToDoList\ToDoListView;

class ToDoListController implements ControllerInterface
{
    public function control(): void
    {
        $view = new ToDoListView();
        $view->render();
    }

    public static function support(string $chemin, string $method): bool
    {
        return $chemin === "/to-do-list" && $method === "GET";
    }
}