<?php

namespace Views\Index;

use Core\Utilis\SessionService;
use Core\AbstractView;
use Controllers\Index\IndexControllerPost;

/**
 * Class IndexView
 *
 * Represents the view responsible for displaying and rendering
 * the index page of the SAE Manager application.
 *
 * This class extends {@see AbstractView} and defines methods to handle
 * the display of error and success messages, as well as the configuration
 * of the page’s title, CSS file, and additional HTML headers.
 *
 * @category   View
 * @package    Src
 * @subpackage Views\Index
 * @author     Alexandre Benhafessa <alexandre.benhafessa@etu.univ-amu.fr>
 * @author     François Dargentolle <francois.dargentolle@etu.univ-amu.fr>
 * @author     William Edelstein <william.edelstein@etu.univ-amu.fr>
 * @author     Nathan Griguer <nathan.griguer@etu.univ-amu.fr>
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class IndexView extends AbstractView
{
    /**
     * Path to the HTML template file used for rendering the index page.
     *
     * @var string
     */
    private const TEMPLATE_HTML = __DIR__ . '/index.html';

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
     * Returns an associative array of keys and values to be used in the HTML template.
     *
     * This method prepares dynamic content by rendering the HTML
     * for error and success messages retrieved from the session.
     *
     * @return array<string, string> An associative array with template keys for messages.
     */
    protected function templateKeys(): array
    {
        $errors = $this->data['errors'];

        return [
            'ERROR_MESSAGES' => $this->renderErrorMessages($errors),
            'SUCCESS_MESSAGE' => $this->renderSuccessMessage(),
        ];
    }


    /**
     * Returns the name of the CSS file used by the index page.
     *
     * @return string The CSS filename.
     */
    protected function getNameCss(): string
    {
        return 'index.css';
    }

    /**
     * Returns additional HTML headers for the index page.
     *
     * These include meta tags for SEO, authorship, and Open Graph (OG) integration
     * for Facebook, LinkedIn, and Instagram.
     *
     * @return string The HTML string containing additional meta headers.
     */
    protected function getAdditionalHeaders(): string
    {
        return '<meta name="description" content="Page d\'accueil de SAE Manager">
                <meta name="keywords" content="SAE Manager, Accueil, Gestion">
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
