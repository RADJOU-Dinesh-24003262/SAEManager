<?php

namespace Controllers\Index;

use Controllers\ControllerInterface;
use Views\Index\IndexView;

/**
 * Class User

 * @package src

 * @subpackage Controllers\PageSae

 * @author Benhafessa Alexandre, Dargentolle Francois, Edelstein William, Griguer Nathan, Radjou Dinesh

 * This class controls the Index page.
 */
class IndexController implements ControllerInterface
{
    /**
     * Principal manager of the controller
     *
     * @return void
     */
    public function control(): void
    {
        $view = new IndexView();
        $view->render();
    }

    /**
     * Check if this controller can handle the request
     *
     * @return boolean Is the method get?
     */
    public static function support(string $chemin, string $method): bool
    {
        return ($chemin === "/index" || $chemin === "/" ) && $method === "GET";
    }
}
