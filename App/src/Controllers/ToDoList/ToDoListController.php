<?php

namespace Controllers\ToDoList;

use Core\ControllerInterface;
use Views\ToDoList\ToDoListView;

/**
 * Handles the control logic for the To-Do List page.
 *
 * @category   Controllers
 * @package    Src
 * @subpackage Controllers/ToDoList
 *
 * @author Alexandre Benhafessa <alexandre.benhafessa@etu.univ-amu.fr>
 * @author François Dargentolle <francois.dargentolle@etu.univ-amu.fr>
 * @author William Edelstein <william.edelstein@etu.univ-amu.fr>
 * @author Nathan Griguer <nathan.griguer@etu.univ-amu.fr>
 * @author Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 *
 * @license MIT License https://opensource.org/licenses/MIT
 *
 * @link https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class ToDoListController implements ControllerInterface
{
    /**
     * @method void control() Controls the rendering of the To-Do List view.
     *
     * @return void
     */
    public function control(): void
    {
        $view = new ToDoListView();
        $view->render();
    }

    /**
     * Determines if this controller supports the given path and method.
     *
     * @method static bool support(string $path, string $method)
     * @param  string $path   Add the path to consult the page.
     * @param  string $method Add the kind of method to consult the page.
     * @return boolean True if the path and method are supported, false otherwise.
     */
    public static function support(string $path, string $method): bool
    {
        return $path === "/to-do-list" && $method === "GET";
    }
}
