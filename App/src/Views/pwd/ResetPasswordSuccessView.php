<?php

namespace Views\pwd;

use Core\AbstractView;

/**
 * Class ResetPasswordSuccessView
 *
 * Represents the view responsible for rendering the "Reset Password Success" page
 * of the SAE Manager application.
 *
 * This class extends {@see AbstractView} and provides specific implementations
 * for rendering the success confirmation page displayed after a password reset.
 *
 * @category   View
 * @package    Src
 * @subpackage Views\pwd
 * @author     Alexandre Benhafessa <alexandre.benhafessa@etu.univ-amu.fr>
 * @author     François Dargentolle <francois.dargentolle@etu.univ-amu.fr>
 * @author     William Edelstein <william.edelstein@etu.univ-amu.fr>
 * @author     Nathan Griguer <nathan.griguer@etu.univ-amu.fr>
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class ResetPasswordSuccessView extends AbstractView
{
    /**
     * Path to the HTML template file for the reset password success page.
     *
     * @var string
     */
    private const TEMPLATE_HTML = __DIR__ . '/reset-password-success.html';

    /**
     * Returns the path to the HTML template file.
     *
     * @return string The full path to the template file.
     */
    protected function templatePath(): string
    {
        return self::TEMPLATE_HTML;
    }

    /**
     * Returns an associative array of keys and values used in the template.
     *
     * In this case, the reset password success page does not require
     * any dynamic data, so the method returns an empty array.
     *
     * @return array<empty> An empty array.
     */
    protected function templateKeys(): array
    {
        return [];
    }

    /**
     * Returns the title of the "Reset Password Success" page.
     *
     * This title may be used in the HTML `<title>` tag or for accessibility.
     *
     * @return string The page title.
     */
    protected function getPageTitle(): string
    {
        return 'Mot de passe réinitialisé - SAE Manager';
    }

    /**
     * Returns the name of the CSS file associated with this view.
     *
     * Used by the parent layout to include the correct stylesheet.
     *
     * @return string The CSS filename.
     */
    protected function getNameCss(): string
    {
        return 'reset-password-success.css';
    }

    /**
     * Returns additional JavaScript code for the success page.
     *
     * This script displays a countdown timer and redirects the user
     * to the login page after a few seconds.
     *
     * @return string The HTML script tag containing JavaScript code.
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

    /**
     * Returns additional HTML meta headers for the "Reset Password Success" page.
     *
     * Includes SEO metadata and Open Graph (OG) tags for social media.
     *
     * @return string The HTML string containing additional meta headers.
     */
    protected function getAdditionalHeaders(): string
    {
        return '<meta name="description" content="Page de succès de réinitialisation du mot de passe de SAE Manager">
                <meta name="keywords" content="SAE Manager, Réinitialisation, Mot de passe, Succès">
                <meta name="author" content="Benhafessa-Edelstein-Dargentolle-Griguer-Radjou">

                <meta property="og:title" content="Notre site" />
                <meta property="og:url" content="http://www.facebook.com/" />
                <meta property="og:description" content="Pour en savoir plus sur nous" />
                <meta property="og:site_name" content="SAE Manager" />
                <meta property="og:type" content="website" />

                <meta property="og:title" content="Notre site" />
                <meta property="og:url" content="http://www.linkedin.com/" />
                <meta property="og:description" content="Pour en savoir plus sur nous" />
                <meta property="og:site_name" content="SAE Manager" />
                <meta property="og:type" content="website" />

                <meta property="og:title" content="Notre site" />
                <meta property="og:url" content="http://www.instagram.com/" />
                <meta property="og:description" content="Pour en savoir plus sur nous" />
                <meta property="og:site_name" content="SAE Manager" />
                <meta property="og:type" content="website" />';
    }
}
