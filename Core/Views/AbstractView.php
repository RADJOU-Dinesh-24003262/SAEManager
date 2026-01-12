<?php

namespace Core\Views;

use Core\Utilis\SessionService;
use Exception;

/**
 * The abstract class which will be used to create all of the views.
 *
 * It contains all the required methods and attributes to be used in the implemented views.
 *
 * @category View

 * @package Src
 *
 * @author Alexandre Benhafessa <alexandre.benhafessa@etu.univ-amu.fr>
 * @author François Dargentolle <francois.dargentolle@etu.univ-amu.fr>
 * @author William Edelstein <william.edelstein@etu.univ-amu.fr>
 * @author Nathan Griguer <nathan.griguer@etu.univ-amu.fr>
 * @author Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>

 * @license MIT License https://opensource.org/licenses/MIT

 * @link https://github.com/RADJOU-Dinesh-24003262/SAEManager
 **/
abstract class AbstractView
{
    /**
     * Stores the data used in the implemented page. The var line contains the type stored in this variable.
     *
     * @var array<string, mixed>
     */
    protected array $data = [];

    /**
     * Initializes the $data attribute with the array of data given when called.
     *
     * @param array<string, mixed> $data The array of data to be instantiated.

     * @return void Creates The instance of the class.
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
        $errors = SessionService::getFlash('errors', []);
        if (is_string($errors)) {
            $errors = [$errors];
        }
        $this->data['errors'] = $errors;
        $this->data['success'] = SessionService::getFlash('success', '');
    }

    /**
     * Renders an HTML template by replacing predefined placeholders with actual values.
     *
     * This method retrieves an HTML template and replaces placeholders with values
     * returned by the templateKeys() method.
     *
     * @return void
     * @throws Exception If the themplate not found.
     */
    protected function renderBody(): void
    {
        $template = file_get_contents($this->templatePath());

        if ($template === false) {
            throw new Exception("Une eurreur est survenu lors la chargement de la page");
        }

        // Replacement of template keys with actual values.
        foreach ($this->templateKeys() as $key => $value) {
            $template = str_replace("{{{$key}}}", $value, $template);
        }

        echo $template;
    }

    /**
     * Returns the path to the HTML template file.
     *
     * @return string
     */
    abstract protected function templatePath(): string;

    /**
     * Returns an associative array of keys and values to be used in the HTML template.
     *
     * This method retrieves error messages and success messages from the session
     * and prepares them for rendering in the template.
     *
     * @return array<string, mixed> An associative array with keys for error and success messages.
     */
    abstract protected function templateKeys(): array;

    /**
     * Renders the complete HTML page including header, body, and footer.
     *
     * This method orchestrates the rendering of the entire HTML page by calling
     * the methods to render the header, body, and footer in sequence.
     *
     * @return void
     */
    public function render(): void
    {
        $this->renderHeader();
        $this->renderBody();
        $this->renderFooter();
    }

    /**
     * Renders the HTML header section of the page.
     *
     * This method outputs the HTML for the header section, including meta tags,
     * title, CSS links, and navigation bar.
     *
     * @return void
     */
    protected function renderHeader(): void
    {
        echo '<!DOCTYPE html>
<html lang="fr" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . $this->getPageTitle() . '</title>
    <link rel="icon" type="image/x-icon" href="/image/favicon.ico">

    <link rel="stylesheet" href="/styles/pico.classless.blue.css">

    <link rel="stylesheet" href="/styles/header.css">
    <link rel="stylesheet" href="/styles/footer.css">

    ' . $this->getAdditionalHeaders() . '
    <link rel="stylesheet" href="/styles/' . $this->getNameCss() . '">
</head>
<body>

    <header class="container-fluid">
        <nav aria-label="breadcrumb" class="main-nav">
            <a href="/" class="nav-logo">
                <img src="/image/logoamu.png" alt="Logo AMU" style="height: 40px;">
            </a>
            <ul class="nav-links">
                ' . $this->getNavBar() . '
            </ul>
        </nav>
    </header>

    <main class="container-fluid">
';
    }


    /**
     * Returns the name of the CSS file associated with the view.
     *
     * This method should be implemented by subclasses to specify the CSS file
     * that should be included in the HTML header for styling the page.
     *
     * @return string The name of the CSS file.
     */
    abstract protected function getNameCss(): string;

    /**
     * Renders the HTML footer section of the page.
     *
     * This method outputs the HTML for the footer section, including contact information
     * and social media links.
     *
     * @return void
     */
    protected function renderFooter(): void
    {
        echo '
    </main>
    <footer class="container-fluid">
        <hr>
        <nav>
            <ul>
                <li>
                    <img src="/image/logoamu.png" alt="Logo AMU" style="height: 35px; margin-right: 10px;">
                    <strong>SAEManager</strong>
                </li>
            </ul>

            <ul>
                <li>+33 02 50 65 14 4 </li>
                <li>📧 <a href="mailto:sae.manager@gmail.com">Email</a></li>
            </ul>

            <ul>
                <li><a href="#" class="secondary">Instagram</a></li>
                <li><a href="#" class="secondary">Facebook</a></li>
                <li><a href="#" class="secondary">LinkedIn</a></li>
            </ul>

            <ul>
                <li><a href="/legal-notice" class="secondary">Mentions légales</a></li>
                <li><a href="/site-map" class="secondary">Plan du site</a></li>
            </ul>
        </nav>
    </footer>

    ' . $this->getAdditionalScripts() . '
</body>
</html>';
    }

    /**
     * Returns the name of the project 'SAE Manager' or be used in some cases like displaying it by some isolated texts.

     * @return string the name of the project 'SAE Manager'.
     */
    protected function getNavBar(): string
    {
        if (SessionService::has('user_id')) {
            return '
                <li><a href="/dashboard">Dashboard</a></li>
                <li><a href="/logout">Déconnexion</a></li>';
        }
        return '
                <li><a href="/">Accueil</a></li>
                <li><a href="/login">Connexion</a></li>
                <li><a href="/register">Inscription</a></li>';
    }

    /**
     * Returns the name of the project 'SAE Manager' or be used in some cases like displaying it by some isolated texts.

     * @return string the name of the project 'SAE Manager'.
     */
    protected function getPageTitle(): string
    {
        return 'SAE Manager';
    }

    /**
     * Returns additional HTML headers associated with the view.
     *
     * @return string The additional HTML headers.
     */
    protected function getAdditionalHeaders(): string
    {
        return '';
    }

    /**
     * Returns additional scripts to be included before closing a body tag.
     *
     * @return string The additional scripts.
     */
    protected function getAdditionalScripts(): string
    {
        return '';
    }

    /**
     * Renders error messages in HTML format.
     *
     * @param array<string|integer, string> $errors List of error messages.
     *
     * @return string The rendered HTML or an empty string.
     */
    protected function renderErrorMessages(array $errors): string
    {
        if (empty($errors)) {
            return '';
        }

        $html = '<article role="alert" style="background-color: var(--pico-del-color); color: white; padding: 1rem; ' .
            'border-radius: 0.5rem; margin-bottom: 1rem;"><ul style="margin: 0; padding-left: 1.5rem;">';
        foreach ($errors as $error) {
            $html .= '<li>' . $error . '</li>';
        }
        $html .= '</ul></article>';
        return $html;
    }

    /**
     * Renders a success message in HTML format if available.
     *
     * @return string The rendered HTML or an empty string.
     */
    protected function renderSuccessMessage(): string
    {
        $success = $this->data['success'] ?? '';
        if (empty($success)) {
            return '';
        }
        return '<article role="status" style="background-color: var(--pico-ins-color); color: white; padding: 1rem; ' .
            'border-radius: 0.5rem; margin-bottom: 1rem;">' . htmlspecialchars($success) . '</article>';
    }
}
