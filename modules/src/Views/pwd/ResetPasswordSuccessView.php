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
}