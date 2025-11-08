<?php

namespace Views\Index;

use Core\Utilis\SessionService;
use Core\AbstractView;
use Controllers\Index\IndexControllerPost;

/**
 * Class IndexView
 *
 * Represents the view responsible for displaying and rendering
 * the index page of the SAEManager application.
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
        $success = $this->data['success'];

        return [
            'ERROR_MESSAGES' => $this->renderErrorMessages($errors),
            'SUCCESS_MESSAGE' => $this->renderSuccessMessage($success),
        ];
    }

    /**
     * Renders HTML markup for displaying error messages to the user.
     *
     * If the provided array of errors is empty, an empty string is returned.
     *
     * @param array<string> $errors The list of error messages to display.
     *
     * @return string The HTML markup for error messages, or an empty string if none.
     */
    private function renderErrorMessages(array $errors): string
    {
        if (empty($errors)) {
            return '';
        }

        $html = '<div class="alert alert-error"><ul>';
        foreach ($errors as $error) {
            $html .= '<li>' . $error . '</li>';
        }
        $html .= '</ul></div>';

        return $html;
    }

    /**
     * Renders HTML markup for displaying a success message to the user.
     *
     * If the provided success message is empty, an empty string is returned.
     *
     * @param string $success The success message to display.
     *
     * @return string The HTML markup for the success message, or an empty string if none.
     */
    private function renderSuccessMessage(string $success): string
    {
        if (empty($success)) {
            return '';
        }

        return '<div class="alert alert-success"><p>' . $success . '</p></div>';
    }

    /**
     * Returns the title of the index page.
     *
     * @return string The page title.
     */
    protected function getPageTitle(): string
    {
        return 'Index - SAEManager';
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
        return '<meta name="description" content="Page d\'accueil de SAEManager">
                <meta name="keywords" content="SAEManager, Accueil, Gestion">
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
                <meta property="og:type" content="website" />';
    }
}
