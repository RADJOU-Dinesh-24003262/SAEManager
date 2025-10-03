<?php   

namespace Views\Info;

use Views\AbstractView;

class LegalNoticeView extends AbstractView
{
    private const TEMPLATE_HTML = __DIR__ . '/legalNotice.html';


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
        return 'Mentions Légales - SAEManager';
    }

    protected function getNameCss(): string
    {
        return 'legal-notice.css';
    }
}

