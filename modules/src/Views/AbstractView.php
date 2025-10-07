<?php
namespace Views;

abstract class AbstractView 
{
    protected array $data = [];

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    protected function renderBody(): void
    {
        $template = file_get_contents($this->templatePath());
        
        // Remplacement des clés du template
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

    /** Returns an associative array of keys and values to be used in the HTML template.
     *
     * This method retrieves error messages and success messages from the session
     * and prepares them for rendering in the template.
     *
     * @return array An associative array with keys for error and success messages.
     */
    abstract protected function templateKeys(): array;
    /** Renders the complete HTML page including header, body, and footer.
     *
     * This method orchestrates the rendering of the entire HTML page by calling
     * the methods to render the header, body, and footer in sequence.
     */
    public function render(): void
    {
        $this->renderHeader();
        $this->renderBody();
        $this->renderFooter();
    }
    /** Renders the HTML header section of the page.
     *
     * This method outputs the HTML for the header section, including meta tags,
     * title, CSS links, and navigation bar.
     */
    protected function renderHeader(): void
    {
        echo '<!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>' . $this->getPageTitle() . '</title>
            <link rel="icon" type="image/x-icon" href="/image/favicon.ico">
            <link rel="stylesheet" href="styles/'.$this->getNameCss().'">
            <link rel="stylesheet" href="styles/header.css">
            ' . $this->getAdditionalHeaders() . '
        </head>
        <body>
        <header class="global-header">
            <h1 class="saeManager">SAEManager</h1>
                <img src="/image/logoamu.png" alt="Logo AMU Header">
            <nav class="navBar">
            <a href="/" class="nav-link">Accueil</a>
            <a href="/login" class="nav-link">Connexion</a>
            <a href="/register" class="nav-link">Inscription</a>
    </nav>
</header>

        ';
    }
    /** Returns the name of the CSS file associated with the view.
     *
     * This method should be implemented by subclasses to specify the CSS file
     * that should be included in the HTML header for styling the page.
     *
     * @return string The name of the CSS file.
     */
    abstract protected function getNameCss(): string;
    /** Renders the HTML footer section of the page.
     *
     * This method outputs the HTML for the footer section, including contact information
     * and social media links.
     */
    protected function renderFooter(): void
    {
        echo $this->getAdditionalScripts() . '
        <footer>
        <link rel="stylesheet" href="styles/footer.css">
            <div class="footer-container">
                <div class="footer-left">
                    <h1 class="saeManager">SAEManager</h1>
                    <img src="/image/logo-footer.png" alt="AMU Logo" class="footer-logo-amu">
                </div>

                <div class="footer-middle">
                    <h1>Nous contacter :</h1>
                    <ul>
                        <li>📞 Tel : +33 02 50 65 14 4</li>
                        <li>📧 Mail : sae.manager@gmail.com</li>
                    </ul>
                    
                    <ul>
                        <li><a href="/legal-notice">Mentions légales</a> </li>
                    </ul>
                </div>

                <div class="footer-right">
                    <h1>Nous suivre :</h1>
                    <ul>
                        <li>Instagram</li>
                        <li>Facebook</li>
                        <li>LinkedIn</li>
                    </ul>
                </div>
            </div>
        </footer>
    </body>
</html>';
    }

    protected function getPageTitle(): string
    {
        return 'SAEManager';
    }
    /** Returns additional HTML headers for any view class page which will extend this class.
     *
     * @return string The additional HTML headers.
     */
    protected function getAdditionalHeaders(): string
    {
        return '';
    }
    /** Returns additional scripts to be included before closing a body tag.
     *
     * @return string The additional scripts.
     */
    protected function getAdditionalScripts(): string
    {
        return '';
    }
}