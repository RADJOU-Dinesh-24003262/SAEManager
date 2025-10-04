<?php

namespace Views\Index;

use Utilis\SessionService;
use Views\AbstractView;
use Controllers\Index\IndexControllerPost;
class IndexView extends AbstractView
{

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
            $html .= '<li>' . $this->$error . '</li>';
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
}