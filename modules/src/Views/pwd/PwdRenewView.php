<?php

namespace Views\pwd;
use Utilis\SessionService;
use Views\AbstractView;

class PwdRenewView extends AbstractView
{
    // Constant for the form
    public const FIELD_PASSWORD = 'pwd';
    public const FIELD_PASSWORD_CONFIRM = 'pwdverif';

    private const TEMPLATE_HTML = __DIR__ . '/pwd_renew.html';

    public function __construct()
    {
        $data = [
            'errors' => SessionService::getFlash('errors', []),
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


    protected function getPageTitle(): string
    {
        return 'Password Renew - SAEManager';
    }

    protected function getNameCss(): string
    {
        return 'pwd-renew.css';
    }

}