<?php   

namespace Views\Info;

use Views\AbstractView;

/**
 * Class LegalNoticeView
 *
 * This class represents the view for the legal notice page of the application.
 * It extends the AbstractView class and provides specific implementations
 * for rendering the legal notice page.
 */
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
    /** Returns additional HTML headers for the legal Notice page.
     *
     * @return string The additional HTML headers.
     */
    protected function getAdditionalHeaders(): string
    {
        return '<meta name="description" content="Mentions légales de SAEManager">
                <meta name="keywords" content="SAEManager, Mentions légales">
                <meta name="author" content="Benhafessa-Edelstein-Dargentolle-Griguer-Radjou">';
    }
}

