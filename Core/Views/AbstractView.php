<?php

namespace Core\Views;

use Core\Utilis\SessionService;
use Exception;

/**
 * The abstract class which will be used to create all of the views.
 */
abstract class AbstractView
{
    /**
     * Stores the data used in the implemented page.
     * @var array<string, mixed>
     */
    protected array $data = [];

    /**
     * Initializes the $data attribute with flash messages.
     * @param array<string, mixed> $data
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
     * Renders the complete HTML page including header, body, and footer.
     */
    public function render(): void
    {
        $this->renderHeader();
        $this->renderBody();
        $this->renderFooter();
    }

    /**
     * Renders the HTML header section of the page.
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
     * Renders the HTML footer section of the page.
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
     * Renders an HTML template by replacing placeholders.
     */
    protected function renderBody(): void
    {
        $template = file_get_contents($this->templatePath());

        if ($template === false) {
            throw new Exception("Une erreur est survenue lors du chargement de la page");
        }

        foreach ($this->templateKeys() as $key => $value) {
            $template = str_replace("{{{$key}}}", $value, $template);
        }

        echo $template;
    }

    /**
     * Returns the HTML navigation bar items.
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

    abstract protected function templatePath(): string;
    abstract protected function templateKeys(): array;
    abstract protected function getNameCss(): string;

    protected function getPageTitle(): string
    {
        return 'SAE Manager';
    }

    protected function getAdditionalHeaders(): string
    {
        return '';
    }

    protected function getAdditionalScripts(): string
    {
        return '';
    }

    protected function renderErrorMessages(array $errors): string
    {
        if (empty($errors)) {
            return '';
        }

        $html = '<article role="alert" style="background-color: var(--pico-del-color); color: white; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem;"><ul style="margin: 0; padding-left: 1.5rem;">';
        foreach ($errors as $error) {
            $html .= '<li>' . htmlspecialchars($error) . '</li>';
        }
        $html .= '</ul></article>';
        return $html;
    }

    protected function renderSuccessMessage(): string
    {
        $success = $this->data['success'] ?? '';
        if (empty($success)) {
            return '';
        }
        return '<article role="status" style="background-color: var(--pico-ins-color); color: white; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem;">' . htmlspecialchars($success) . '</article>';
    }
}
