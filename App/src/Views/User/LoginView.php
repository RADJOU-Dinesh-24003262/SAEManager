<?php

namespace Views\User;

use Core\AbstractView;
use Core\Utilis\SessionService;

/**
 * Class LoginView
 *
 * Represents the login page view.
 * Extends the AbstractView abstract class and implements required methods
 * to display the login form and possible error messages.
 *
 * @category   View
 * @package    Src
 * @subpackage Views\User
 *
 * @author Alexandre Benhafessa <alexandre.benhafessa@etu.univ-amu.fr>
 * @author François Dargentolle <francois.dargentolle@etu.univ-amu.fr>
 * @author William Edelstein <william.edelstein@etu.univ-amu.fr>
 * @author Nathan Griguer <nathan.griguer@etu.univ-amu.fr>
 * @author Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>

 * @license MIT License https://opensource.org/licenses/MIT

 * @link https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class LoginView extends AbstractView
{
    /**
     * @var string Path to the HTML template
     */
    private const TEMPLATE_HTML = __DIR__ . '/loginview.html';


    /**
     * Returns the path to the HTML template file.
     *
     * @return string Template file path.
     */
    protected function templatePath(): string
    {
        return self::TEMPLATE_HTML;
    }

    /**
     * Returns the template keys to be used in the view.
     *
     * Includes the rendered error messages if any.
     *
     * @return array<string, string> Template keys and their values.
     */
    protected function templateKeys(): array
    {
        $errors = $this->data['errors'] ?? [];

        return [
            'ERROR_MESSAGES' => $this->renderErrorMessages($errors),
        ];
    }

    /**
     * Renders error messages as an HTML list.
     *
     * Applies htmlspecialchars to prevent XSS.
     *
     * @param array<int, string> $errors List of error messages.
     *
     * @return string HTML string of formatted error messages or empty string.
     */
    private function renderErrorMessages(array $errors): string
    {
        if (empty($errors)) {
            return '';
        }

        $html = '<div class="alert alert-error"><ul>';
        foreach ($errors as $error) {
            $html .= '<li>' . htmlspecialchars($error, ENT_QUOTES, 'UTF-8') . '</li>';
        }
        $html .= '</ul></div>';

        return $html;
    }

    /**
     * Returns the name of the CSS file to include for this view.
     *
     * @return string CSS filename.
     */
    protected function getNameCss(): string
    {
        return 'style.css';
    }

    /**
     * Returns additional meta headers for the login page.
     *
     * @return string Additional HTML meta tags.
     */
    protected function getAdditionalHeaders(): string
    {
        return '<meta name="description" content="Page de connexion de SAEManager">
                <meta name="keywords" content="SAEManager, Connexion">
                <meta name="author" content="Benhafessa-Edelstein-Dargentolle-Griguer-Radjou">
                <meta property="og:title" content="Notre site" />
                <meta property="og:url" content="http://www.facebook.com/" />
                <meta property="og:description" content="Pour en savoir plus sur nous" />
                <meta property="og:site_name" content="SAEManager" />
                <meta property="og:type" content="website" />
                <meta property="og:url" content="http://www.linkedin.com/" />
                <meta property="og:url" content="http://www.instagram.com/" />';
    }
}
