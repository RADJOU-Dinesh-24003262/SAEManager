<?php
namespace Controllers\Info;

use Controllers\ControllerInterface;
use Views\Info\SiteMapView;

/**
 * Class User
 
 * @package     src

 * @subpackage  Controllers\Info

 * @author      Benhafessa Alexandre, Dargentolle Francois, Edelstein William, Griguer Nathan, Radjou Dinesh

 * This class controls the site map page.
 */
class SiteMapController implements ControllerInterface
{
    /**
     * Principal manager of the controller
     * 
     * @return void
     */
    public function control(): void
    {
        $view = new SiteMapView();
        $view->render();
    }

    /**
     * Check if this controller can handle the request
     * 
     * @return boolean Is the method get?
     */
    public static function support(string $chemin, string $method): bool
    {
        return $chemin === "/site-map" && $method === "GET";
    }
}