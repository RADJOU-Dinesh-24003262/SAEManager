<?php

namespace Controllers\Info;

use Controllers\ControllerInterface;
use Views\Info\LegalNoticeView;

/**
 * Class User

 * @package     src

 * @subpackage  Controllers\PageSae

 * @author      Benhafessa Alexandre, Dargentolle Francois, Edelstein William, Griguer Nathan, Radjou Dinesh

 * This class controls the legal notice page.
 */
class LegalNoticeController implements ControllerInterface
{
    /**
     * Principal manager of the controller
     *
     * @return void
     */
    public function control(): void
    {
        $view = new LegalNoticeView();
        $view->render();
    }

    /**
     * Check if this controller can handle the request
     *
     * @return boolean Is the method get?
     */
    public static function support(string $chemin, string $method): bool
    {
        return $chemin === "/legal-notice" && $method === "GET";
    }
}
