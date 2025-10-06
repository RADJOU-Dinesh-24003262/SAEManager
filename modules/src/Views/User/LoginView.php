<?php

namespace Views\User;

use Views\AbstractView;
use Utilis\SessionService;

/**
 * Class LoginView
 *
 * This class represents the view for the login page of the application.
 * It extends the AbstractView class and provides specific implementations
 * for rendering the login page, including handling error messages.
 */
class LoginView extends AbstractView
{
    private const TEMPLATE_HTML = __DIR__ . '/loginview.html';

    protected function templatePath(): string
    {
        return self::TEMPLATE_HTML;
    }
    protected function templateKeys(): array
    {
        $errors = $this->data['errors'];

        return [
            // Messages d'erreur
            'ERROR_MESSAGES' => $this->renderErrorMessages($errors)
        ];
    }
    public function __construct()
    {
        $data = [
            'errors' => SessionService::getFlash('errors', []),
        ];
        parent::__construct($data);
    }
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

    protected function getNameCss(): string
    {
        return 'style.css';
    }
    /** Returns additional HTML headers for the Login page.
     *
     * @return string The additional HTML headers.
     */
    protected function getAdditionalHeaders(): string
    {
        return '<meta name="description" content="Page de connexion de SAEManager">
                <meta name="keywords" content="SAEManager, Connexion">
                <meta name="author" content="Benhafessa-Edelstein-Dargentolle-Griguer-Radjou">';
    }
}