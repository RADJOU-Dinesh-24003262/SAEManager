<?php

namespace Controllers\PageSae;

use Controllers\ControllerInterface;
use Views\PageSAE\PageSaeView;

/**
 * Class User

 * @package     src

 * @subpackage  Controllers\PageSae

 * @author      Benhafessa Alexandre, Dargentolle Francois, Edelstein William, Griguer Nathan, Radjou Dinesh

 * This class controls the SAE page.
 */
class PageSaeController implements ControllerInterface
{
    /**
     * Principal manager of the controller
     *
     * @return void
     */
    public function control(): void
    {
        $view = new PageSaeView();
        $view->render();
    }

    /**
     * Check if this controller can handle the request
     *
     * @return boolean Is the method get?
     */
    public static function support(string $chemin, string $method): bool
    {
        return $chemin === "/page-sae" && $method === "GET";
    }
}
