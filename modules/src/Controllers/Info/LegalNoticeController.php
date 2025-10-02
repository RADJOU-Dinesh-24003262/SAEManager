<?php
namespace Controllers\Info;

use Controllers\ControllerInterface;
use Views\Info\LegalNoticeView;

class LegalNoticeController implements ControllerInterface
{
    public function control(): void
    {
        $view = new LegalNoticeView();
        $view->render();
    }

    public static function support(string $chemin, string $method): bool
    {
        return $chemin === "/legal-notice" && $method === "GET";
    }
}