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
    /** Returns additional HTML headers for the site-map page.
     *
     * @return string The additional HTML headers.
     */
    protected function getAdditionalHeaders(): string
    {
        return '<meta name="description" content="Plan du Site de SAEManager">
                <meta name="keywords" content="SAEManager, Plan du Site">
                <meta name="author" content="Benhafessa-Edelstein-Dargentolle-Griguer-Radjou">';
    }
}

