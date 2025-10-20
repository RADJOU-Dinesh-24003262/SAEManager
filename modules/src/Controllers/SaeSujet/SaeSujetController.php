<?php
namespace Controllers\SaeSujet;
/**
 * Controller for the form of the subject of the SAE.
 * Handles requests to display the form.
 * @package Controllers\SaeSujet
 * @version 1.0
 * @author Dargentolle François
 * @see SaeSujetView
 * @link /sae-sujet
 * @category Controller
 *
 */
use Controllers\ControllerInterface;
use Views\SaeSujet\SaeSujetView;
use Views\ToDoList\ToDoListView;
/**
 * Class SaeSujetController
 * Handles the control logic for the form of the subject of the SAE.
 * @package Controllers\SaeSujet
 * @version 1.0
 * @author Dargentolle François
 * @see SaeSujetView
 * @link /sae-sujet
 * @category Class
 * @implements ControllerInterface
 */
class SaeSujetController implements ControllerInterface
{
    /**
     * @method void control() Controls the rendering of the form of subject view.
     * @var SaeSujetView $view used to initialize the view for the form of subject
     */
    public function control(): void
    {
        $view = new SaeSujetView();
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

        return $path === "/sae-sujet" && $method === "GET";
    }
}