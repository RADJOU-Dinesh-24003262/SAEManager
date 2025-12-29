<?php

namespace Views\Info;

use Core\Views\AbstractView;
use Override;

/**
 * Class SiteMapView
 * This class represents the view for the Site map page of the application.
 * It extends the AbstractView class and provides specific implementations
 * for rendering the site map page.
 *
 * @category   View
 * @package    Src
 * @subpackage Views/Info
 * @author     Alexandre Benhafessa <alexandre.benhafessa@etu.univ-amu.fr>
 * @author     François Dargentolle <francois.dargentolle@etu.univ-amu.fr>
 * @author     William Edelstein <william.edelstein@etu.univ-amu.fr>
 * @author     Nathan Griguer <nathan.griguer@etu.univ-amu.fr>
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
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
    #[Override]
    protected function templatePath(): string
    {
        return self::TEMPLATE_HTML;
    }

    /**
     * Returns an empty array. Implemented from the parent class.
     *
     * This method returns an empty array.
     *
     * @return array<string, mixed> An empty array
     */
    #[Override]
    protected function templateKeys(): array
    {
        return [];
    }

    /**
     * Returns the name of the page 'Plan du Site - SAE Manager' or
     * be used in some cases like displaying it by some isolated texts.

     * @return string the name of the project 'Plan du Site - SAE Manager'.
     */
    #[Override]
    protected function getPageTitle(): string
    {
        return 'Plan du Site - SAE Manager';
    }

    /**
     * Returns the name of the CSS file associated with the view.
     *
     * This method should be implemented by subclasses to specify the CSS file
     * that should be included in the HTML header for styling the page.
     *
     * @return string The name of the CSS file.
     */
    #[Override]
    protected function getNameCss(): string
    {
        return 'plan-site.css';
    }
    /**
     * Returns additional HTML headers for the site-map page.
     *
     * @return string The additional HTML headers.
     */
    #[Override]
    protected function getAdditionalHeaders(): string
    {
        return '<meta name="description" content="Plan du Site de SAE Manager">
                <meta name="keywords" content="SAE Manager, Plan du Site">
                <meta name="author" content="Benhafessa-Edelstein-Dargentolle-Griguer-Radjou">
                
                <meta property="og:title" content="Notre site" />
                <meta property="og:url" content="http://www.facebook.com/" />
                <meta property="og:description" content="Pour en savoir plus sur nous" />
                <meta property="og:site_name" content="SAE Manager" />
                <meta property="og:type" content="website" />
                
                <meta property="og:title" content="Notre site" />
                <meta property="og:url" content="http://www.linkedin.com/" />
                <meta property="og:description" content="Pour en savoir plus sur nous" />
                <meta property="og:site_name" content="SAE Manager" />
                <meta property="og:type" content="website" />
                
                <meta property="og:title" content="Notre site" />
                <meta property="og:url" content="http://www.instagram.com/" />
                <meta property="og:description" content="Pour en savoir plus sur nous" />
                <meta property="og:site_name" content="SAE Manager" />
                <meta property="og:type" content="website" />' ;
    }
}
