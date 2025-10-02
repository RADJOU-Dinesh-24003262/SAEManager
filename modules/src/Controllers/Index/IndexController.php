<?php

namespace Controllers\Index;

use Controllers\ControllerInterface;
use Views\Index\IndexView;

class IndexController implements ControllerInterface
{
        public function control(): void
    {
        $view = new IndexView();
        $view->render();
    }

        public static function support(string $chemin, string $method): bool
    {
        return ($chemin === "/index" || $chemin === "/" ) && $method === "GET";
    }
}