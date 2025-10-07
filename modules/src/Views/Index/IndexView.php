<?php

namespace Views\Index;

use Utilis\SessionService;
use Views\AbstractView;
use Controllers\Index\IndexControllerPost;

/**
 * Class IndexView
 *
 * This class represents the view for the index page of the application.
 * It extends the AbstractView class and provides specific implementations
 * for rendering the index page, including handling error and success messages.
 */
class IndexView extends AbstractView
{
    // Chemin vers le template HTML
    private const TEMPLATE_HTML = __DIR__ . '/index.html';
    public function __construct()
{
    $data = [
        'errors' => SessionService::getFlash('errors', []),
        'success' => SessionService::getFlash('success', '')
    ];
    parent::__construct($data);
}

    protected function templatePath(): string
    {
        return self::TEMPLATE_HTML;
    }

    protected function templateKeys(): array
    {
        $errors = $this->data['errors'];

        return [
            // Messages d'erreur
            'ERROR_MESSAGES' => $this->renderErrorMessages($errors),
            'SUCCESS_MESSAGE' => $this->renderSuccessMessage()
        ];
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

    private function renderSuccessMessage(): string
    {
        $success = $this->data['success'];
        if (empty($success)) {
            return '';
        }

        return '<div class="alert alert-success">' . $this->$success . '</div>';
    }
    protected function getPageTitle(): string
    {
        return 'Index - SAEManager';
    }

    protected function getNameCss(): string
    {
        return 'index.css';
    }

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
                <meta property="og:type" content="website" />' ;
    }

}