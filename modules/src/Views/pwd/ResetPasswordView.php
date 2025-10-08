<?php

namespace Views\pwd;
use Utilis\SessionService;
use Views\AbstractView;

class ResetPasswordView extends AbstractView
{

    private const TEMPLATE_HTML = __DIR__ . '/reset-password.html';

    public function __construct(string $token, string $email){
        $data = [
            'errors' => SessionService::getFlash('errors', []),
            'token' => $token,
            'email' => $email
        ];
        parent::__construct($data);
    }

    protected function templatePath(): string
    {
        return self::TEMPLATE_HTML;
    }

    protected function templateKeys(): array
    {
        return [
            'ERROR_MESSAGES' => $this->renderErrorMessages($this->data['errors']),
            'TOKEN' => htmlspecialchars($this->data['token']),
            'EMAIL_DISPLAY' => htmlspecialchars($this->maskEmail($this->data['email']))
        ];
    }

    /**
     * Partially masks the email for security
     * Ex: jean.dupont@etu.univ-amu.fr → j***n.d***t@etu.univ-amu.fr
     */
    private function maskEmail(string $email): string
    {
        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            return $email;
        }

        $localPart = $parts[0];
        $domain = $parts[1];

        // Mask the local part
        if (strlen($localPart) > 4) {
            $masked = substr($localPart, 0, 1) . 
                      str_repeat('*', strlen($localPart) - 2) . 
                      substr($localPart, -1);
        } else {
            $masked = substr($localPart, 0, 1) . str_repeat('*', strlen($localPart) - 1);
        }

        return $masked . '@' . $domain;
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

    protected function getAdditionalScripts(): string
    {
        return '<script src="scripts/reset-password.js"></script>';
    }

    protected function getAdditionalHeaders(): string
    {
        return '<meta name="description" content="Page de réinitialisation du mot de passe de SAEManager">
                <meta name="keywords" content="SAEManager, Réinitialisation, Mot de passe">
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