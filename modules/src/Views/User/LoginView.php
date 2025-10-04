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
}