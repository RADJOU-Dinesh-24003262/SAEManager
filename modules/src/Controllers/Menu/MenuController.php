<?php

namespace Controllers\Menu;

use Controllers\ControllerInterface;
use Views\Menu\MenuView;

class MenuController implements ControllerInterface
{
        public function control(): void
    {
        $view = new MenuView();
        $view->render();
    }

        public static function support(string $chemin, string $method): bool
    {
        return ($chemin === "/menu") && $method === "GET";
    }
}