<?php   

namespace Views\Info;

use Views\AbstractView;

class SiteMapView extends AbstractView
{
    private const TEMPLATE_HTML = __DIR__ . '/SiteMap.html';


    protected function templatePath(): string 
    {
        return self::TEMPLATE_HTML;
    }

    protected function templateKeys(): array 
    {
        return [];
    }

    protected function getPageTitle(): string
    {
        return 'Plan du Site - SAEManager';
    }

    protected function getNameCss(): string
    {
        return 'plan-site.css';
    }
}

