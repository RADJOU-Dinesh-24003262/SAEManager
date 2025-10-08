<?php

namespace Views\User;

use Views\AbstractView;
use Utilis\SessionService;

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
            // Error messages
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