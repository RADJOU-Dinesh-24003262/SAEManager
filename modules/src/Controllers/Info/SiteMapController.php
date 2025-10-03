<?php
namespace Controllers\Info;

use Controllers\ControllerInterface;
use Views\Info\SiteMapView;

class SiteMapController implements ControllerInterface
{
    public function control(): void
    {
        $view = new SiteMapView();
        $view->render();
    }

    public static function support(string $chemin, string $method): bool
    {
        return $chemin === "/site-map" && $method === "GET";
    }
}