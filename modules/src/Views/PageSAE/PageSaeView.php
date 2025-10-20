<?php

namespace Views\PageSAE;

use Views\AbstractView;

use Utilis\SessionService;

/**
 * Class PageSaeView
 *
 * This class represents the view for the page of the application where we will see the SAE .
 * It extends the AbstractView class and provides specific implementations
 * for rendering the SAE page.
 */
class PageSaeView extends AbstractView
{
    private const TEMPLATE_HTML = __DIR__ . '/pageSae.html';


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
        return 'Page SAE - SAEManager';
    }

    protected function getNameCss(): string
    {
        return 'page-sae.css';
    }
    /** Returns additional HTML headers for the Sae page.
     *
     * @return string The additional HTML headers.
     */
    protected function getAdditionalHeaders(): string
    {
        return '<meta name="description" content="Page SAE de SAEManager">
                <meta name="keywords" content="SAEManager, SAE">
                <meta name="author" content="Benhafessa-Edelstein-Dargentolle-Griguer-Radjou">
                
                <meta property="og:title" content="Notre site" />
                <meta property="og:url" content="http://www.facebook.com/" />
                <meta property="og:description" content="Pour en savoir plus sur nous" />
                <meta property="og:site_name" content="SAEManager" />
                <meta property="og:type" content="website" />
                
                <meta property="og:title" content="Notre site" />
                <meta property="og:url" content="http://www.linkedin.com/" />
                <meta property="og:description" content="Pour en savoir plus sur nous" />
                <meta property="og:site_name" content="SAEManager" />
                <meta property="og:type" content="website" />
                
                <meta property="og:title" content="Notre site" />
                <meta property="og:url" content="http://www.instagram.com/" />
                <meta property="og:description" content="Pour en savoir plus sur nous" />
                <meta property="og:site_name" content="SAEManager" />
                <meta property="og:type" content="website" />' ;
    }
}