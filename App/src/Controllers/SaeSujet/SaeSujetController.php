<?php

namespace Controllers\SaeSujet;

use Core\ControllerInterface;
use Views\SaeSujet\SaeSujetView;
use Views\ToDoList\ToDoListView;

/**
 * Controller for the form of the subject of the SAE.
 *  Handles requests to display the form.
 *
 * @category Controllers
 * @package Src
 * @subpackage Controllers/SaeSujet
 * @author  Alexandre Benhafessa <alexandre.benhafessa@etu.univ-amu.fr>
 * @author  François Dargentolle <francois.dargentolle@etu.univ-amu.fr>
 * @author  William Edelstein <william.edelstein@etu.univ-amu.fr>
 * @author  Nathan Griguer <nathan.griguer@etu.univ-amu.fr>
 * @author  Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>

 * @license MIT License https://opensource.org/licenses/MIT

 * @link https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class SaeSujetController implements ControllerInterface
{
    /**
     * @method void control() Controls the rendering of the form of subject view.
     * @return void
     */
    public function control(): void
    {
        $view = new SaeSujetView();
        $view->render();
    }

    /**
     *  @method static bool support(string $chemin, string $method)
     *  Determines if this controller supports the given path and method.
     *  @param string $path   The requested path.
     *  @param string $method The HTTP method.
     *
     *  @return boolean true if the path and method are supported, false otherwise.
     */
    public static function support(string $path, string $method): bool
    {
        return $path === "/new-sae" && $method === "GET";
    }
}
