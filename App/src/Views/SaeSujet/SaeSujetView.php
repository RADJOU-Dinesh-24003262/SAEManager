<?php

namespace Views\SaeSujet;

use Core\AbstractView;
use Core\Utilis\SessionService;

/**
 * Class SaeSujetView
 *
 * Represents the view responsible for displaying and rendering
 * the SAE subject creation page in the SAE Manager application.
 *
 * This class extends {@see AbstractView} and defines the template path,
 * template keys, page title, CSS, and additional HTML headers specific
 * to the SAE subject creation page.
 *
 * @category   View
 * @package    Src
 * @subpackage Views/SaeSujet
 * @author     Alexandre Benhafessa <alexandre.benhafessa@etu.univ-amu.fr>
 * @author     François Dargentolle <francois.dargentolle@etu.univ-amu.fr>
 * @author     William Edelstein <william.edelstein@etu.univ-amu.fr>
 * @author     Nathan Griguer <nathan.griguer@etu.univ-amu.fr>
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class SaeSujetView extends AbstractView
{
    /**
     * Path to the HTML template file used for rendering the SAE subject creation page.
     *
     * @var string
     */
    private const TEMPLATE_HTML = __DIR__ . '/sae-sujet.html';

    /**
     * Returns the path to the HTML template file.
     *
     * @return string The full path to the template file.
     */
    protected function templatePath(): string
    {
        return self::TEMPLATE_HTML;
    }

    /**
     * Returns an array of keys used in the template for dynamic content replacement.
     *
     * @return array<empty> An associative array of template keys and their corresponding values.
     */
    protected function templateKeys(): array
    {
        return [];
    }

    /**
     * Returns the title of the SAE subject creation page.
     *
     * @return string The page title.
     */
    protected function getPageTitle(): string
    {
        return 'Page SAE - Création du sujet de SAE - SAE Manager';
    }

    /**
     * Returns the name of the CSS file used by the SAE subject creation page.
     *
     * @return string The CSS filename.
     */
    protected function getNameCss(): string
    {
        return 'sae-sujet.css';
    }

    /**
     * Returns additional HTML headers for the SAE subject creation page.
     *
     * These include meta tags for SEO, authorship, and Open Graph (OG) integration
     * for Facebook, LinkedIn, and Instagram.
     *
     * @return string The HTML string containing additional meta headers.
     */
    protected function getAdditionalHeaders(): string
    {
        return '<meta name="description" content="Page SAE de SAE Manager partie Création du sujet">
                <meta name="keywords" content="SAE Manager, SAE, création sujet">
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
                <meta property="og:type" content="website" />';
    }
}
