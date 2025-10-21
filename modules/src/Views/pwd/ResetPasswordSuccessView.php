<?php

namespace Views\pwd;

use Views\AbstractView;

/**
 * Class ResetPasswordSuccessView
 *
 * @package     src

 * @subpackage  pwd

 * @author      Benhafessa Alexandre, Dargentolle Francois, Edelstein William, Griguer Nathan, Radjou Dinesh
 *
 * This class represents the view for the reset password success page of the application.
 * It extends the AbstractView class and provides specific implementations
 * for rendering the reset password success page.
 */
class ResetPasswordSuccessView extends AbstractView
{
    /**
     * The path of the HTML code to display for this view.
     * @var string
     */
    private const TEMPLATE_HTML = __DIR__ . '/reset-password-success.html';

    /**
     * Returns the path to the HTML template file.
     *
     * @return string
     */
    protected function templatePath(): string
    {
        return self::TEMPLATE_HTML;
    }

    /** Returns an empty array. Implemented from the parent class.
     *
     * This method returns an empty array.
     *
     * @return array An empty array
     */
    protected function templateKeys(): array
    {
        return [];
    }

    /**

     * Returns the name of the page 'Mot de passe réinitialisé - SAEManager' or
     * be used in some cases like displaying it by some isolated texts.

     * @return string the name of the project 'Mot de passe réinitialisé - SAEManager'.

     */
    protected function getPageTitle(): string
    {
        return 'Mot de passe réinitialisé - SAEManager';
    }

    /** Returns the name of the CSS file associated with the view.
     *
     * This method should be implemented by subclasses to specify the CSS file
     * that should be included in the HTML header for styling the page.
     *
     * @return string The name of the CSS file.
     */
    protected function getNameCss(): string
    {
        return 'reset-password-success.css';
    }

    /** Returns additional HTML headers for the Login page.
     *
     * @return string The additional HTML headers.
     */
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
    /** Returns additional HTML headers for the Reset password success page.
     *
     * @return string The additional HTML headers.
     */
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
