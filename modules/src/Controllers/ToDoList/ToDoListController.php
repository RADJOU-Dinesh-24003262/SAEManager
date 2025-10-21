<?php

namespace Controllers\ToDoList;

/**
 * Controller for the To-Do List page.
 * Handles requests to display the to-do list.
 * @package Controllers\ToDoList
 * @version 1.0
 * @author Dargentolle François
 * @see ToDoListView
 * @link /to-do-list
 * @category Controller
 *
 */

use Controllers\ControllerInterface;
use Views\ToDoList\ToDoListView;

/**
 * Class ToDoListController
 * Handles the control logic for the To-Do List page.
 * @package Controllers\ToDoList
 * @version 1.0
 * @author Dargentolle François
 * @see ToDoListView
 * @link /to-do-list
 * @category Class
 * @implements ControllerInterface
 */
class ToDoListController implements ControllerInterface
{
    /**
     * @method void control() Controls the rendering of the To-Do List view.
     * @var ToDoListView $view used to initialize the view for to-do list
     */
    public function control(): void
    {
        $view = new ToDoListView();
        $view->render();
    }

    /**
     *  @method static bool support(string $chemin, string $method) Determines if this controller supports the given path and method.
     *  @var String $path add the path to consult the page
     *  @var String $method add the kind of method to consult the page
     *  @return bool true if the path and method are supported, false otherwise
     */
    public static function support(string $path, string $method): bool
    {

        return $path === "/to-do-list" && $method === "GET";
    }
}
