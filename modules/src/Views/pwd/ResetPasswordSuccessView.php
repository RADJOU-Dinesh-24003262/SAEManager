<?php
namespace Views\pwd;

use Views\AbstractView;

class ResetPasswordSuccessView extends AbstractView
{
    private const TEMPLATE_HTML = __DIR__ . '/reset-password-success.html';

    protected function templatePath(): string
    {
        return self::TEMPLATE_HTML;
    }

    protected function templateKeys(): array
    {
        return [];
    }

    protected function getPageTitle(): string
    {
        return 'Mot de passe réinitialisé - SAEManager';
    }

    protected function getNameCss(): string
    {
        return 'reset-password-success.css';
    }

    protected function getAdditionalScripts(): string
    {
        return '<script>
            // Redirection automatique après 5 secondes
            let countdown = 5;
            const countdownElement = document.getElementById("countdown");
            
            const timer = setInterval(() => {
                countdown--;
                if (countdownElement) {
                    countdownElement.textContent = countdown;
                }
                
                if (countdown <= 0) {
                    clearInterval(timer);
                    window.location.href = "/login";
                }
            }, 1000);
        </script>';
    }

    protected function getAdditionalHeaders(): string
    {
        return '<meta name="description" content="Page de succès de réinitialisation du mot de passe de SAEManager">
                <meta name="keywords" content="SAEManager, Réinitialisation, Mot de passe, Succès">
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