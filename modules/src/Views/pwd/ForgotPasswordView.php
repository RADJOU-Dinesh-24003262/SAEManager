<?php

namespace Views\pwd;

use Utilis\SessionService;
use Views\AbstractView;

class ForgotPasswordView extends AbstractView
{
    //Constant for of the form
    public const FIELD_EMAIL = 'email';

    private const TEMPLATE_HTML = __DIR__ . '/forgot-password.html';

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
        $success = $this->data['success'];

        return [
            // Messages d'erreur
            'ERROR_MESSAGES' => $this->renderErrorMessages($errors),
            'SUCCESS_MESSAGE' => $this->renderSuccessMessage($success),
        ];
    }

    private function renderErrorMessages(array $errors): string
    {
        if (empty($errors)) {
            return '';
        }

        $html = '<section role="alert" aria-live="assertive" class="alert alert-error"><ul>';
        foreach ($errors as $error) {
            $html .= '<li>' . $error . '</li>';
        }
        $html .= '</ul></section>';

        return $html;
    }

    private function renderSuccessMessage(string $success): string
    {
        if (empty($success)) {
            return '';
        }

        return '<div class="alert alert-success">' . htmlspecialchars($success) . '</div>';
    }

    protected function getPageTitle(): string
    {
        return 'Password Forgot - SAEManager';
    }

    protected function getNameCss(): string
    {
        return 'pwd-forgot.css';
    }

}