<?php

namespace Views\Info;

use Views\AbstractView;

/**
 * Class LegalNoticeView
 *
 * @package src

 * @subpackage Info

 * @author Benhafessa Alexandre, Dargentolle Francois, Edelstein William, Griguer Nathan, Radjou Dinesh

 * This class represents the view for the legal notice page of the application.
 * It extends the AbstractView class and provides specific implementations
 * for rendering the legal notice page.
 */
class SiteMapView extends AbstractView
{
    /**
     * The path of the HTML code to display for this view.
     *
     * @var string
     */
    private const TEMPLATE_HTML = __DIR__ . '/SiteMap.html';


    /**
     * Returns the path to the HTML template file.
     *
     * @return string
     */
    protected function templatePath(): string
    {
        return self::TEMPLATE_HTML;
    }

    /**
     * Returns an empty array. Implemented from the parent class.
     *
     * This method returns an empty array.
     *
     * @return array An empty array
     */
    protected function templateKeys(): array
    {
        return [];
    }

    /**
     * Returns the name of the page 'Plan du Site - SAEManager' or
     * be used in some cases like displaying it by some isolated texts.

     * @return string the name of the project 'Plan du Site - SAEManager'.
     */
    protected function getPageTitle(): string
    {
        return 'Plan du Site - SAEManager';
    }

    /**
     * Returns the name of the CSS file associated with the view.
     *
     * This method should be implemented by subclasses to specify the CSS file
     * that should be included in the HTML header for styling the page.
     *
     * @return string The name of the CSS file.
     */
    protected function getNameCss(): string
    {
        return 'plan-site.css';
    }
    /**
     * Returns additional HTML headers for the site-map page.
     *
     * @return string The additional HTML headers.
     */
    protected function getAdditionalHeaders(): string
    {
        return '<meta name="description" content="Plan du Site de SAEManager">
                <meta name="keywords" content="SAEManager, Plan du Site">
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
