<?php
namespace Controllers\PageSae;;

use Controllers\ControllerInterface;
use Views\PageSAE\PageSaeView;

class PageSaeController implements ControllerInterface
{
    public function control(): void
    {
        $view = new PageSaeView();
        $view->render();
    }

    public static function support(string $chemin, string $method): bool
    {
        return $chemin === "/page-sae" && $method === "GET";
    }
}