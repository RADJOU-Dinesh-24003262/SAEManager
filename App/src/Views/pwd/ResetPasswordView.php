<?php

namespace Views\pwd;

use Core\Views\AbstractView;
use Override;

/**
 * Class ResetPasswordView
 *
 * Represents the view responsible for displaying and rendering
 * the password reset page in the SAE Manager application.
 *
 * This class extends {@see AbstractView} and defines methods to display
 * password reset content, including error messages, the reset token,
 * and masked user email.
 *
 * @category   View
 * @package    Src
 * @subpackage Views/pwd
 * @author     Alexandre Benhafessa <alexandre.benhafessa@etu.univ-amu.fr>
 * @author     François Dargentolle <francois.dargentolle@etu.univ-amu.fr>
 * @author     William Edelstein <william.edelstein@etu.univ-amu.fr>
 * @author     Nathan Griguer <nathan.griguer@etu.univ-amu.fr>
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class ResetPasswordView extends AbstractView
{
    /**
     * Path to the HTML template file used for rendering the reset password page.
     *
     * @var string
     */
    private const TEMPLATE_HTML = __DIR__ . '/reset-password.html';

    /**
     * ResetPasswordView constructor.
     *
     * Initializes the view by retrieving flash errors from the session
     * and storing the provided token and email in the data array.
     *
     * @param string $token The password reset token assigned to the user.
     * @param string $email The email address associated with the password reset request.
     *
     * @return void
     */
    public function __construct(string $token, string $email)
    {
        $data = [
            'token'  => $token,
            'email'  => $email,
        ];

        parent::__construct($data);
    }

    /**
     * Returns the path to the HTML template file.
     *
     * @return string The full path to the template file.
     */
    #[Override]
    protected function templatePath(): string
    {
        return self::TEMPLATE_HTML;
    }

    /**
     * Returns an associative array of keys and values to be used in the HTML template.
     *
     * This method prepares data for rendering in the template, including
     * error messages, the reset token, and the masked user email.
     *
     * @return array<string, string> An associative array containing template keys and values.
     */
    #[Override]
    protected function templateKeys(): array
    {
        /** @var array<int|string, string> $errors */
        $errors = $this->data['errors'];
        /** @var string $token */
        $token = $this->data['token'];
        /** @var string $email */
        $email = $this->data['email'];

        return [
            'ERROR_MESSAGES' => $this->renderErrorMessages($errors),
            'TOKEN'          => $token,
            'EMAIL_DISPLAY'  => $this->maskEmail($email),
        ];
    }

    /**
     * Returns a masked version of the given email address for security purposes.
     *
     * This method replaces all but the first and last character of the local part
     * with asterisks, leaving the domain unchanged.
     *
     * Example:
     * jean.dupont@etu.univ-amu.fr → j***n.d***t@etu.univ-amu.fr
     *
     * @param string $email The email address to be masked.
     *
     * @return string The masked version of the email address.
     */
    private function maskEmail(string $email): string
    {
        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            return $email;
        }

        $localPart = $parts[0];
        $domain = $parts[1];

        // Mask the local part.
        if (strlen($localPart) > 4) {
            $masked = substr($localPart, 0, 1)
                . str_repeat('*', strlen($localPart) - 2)
                . substr($localPart, -1);
        } else {
            $masked = substr($localPart, 0, 1)
                . str_repeat('*', strlen($localPart) - 1);
        }

        return $masked . '@' . $domain;
    }

    /**
     * Returns the title of the reset password page.
     *
     * @return string The page title.
     */
    #[Override]
    protected function getPageTitle(): string
    {
        return 'Password Renew - SAE Manager';
    }

    /**
     * Returns the name of the CSS file used by the reset password page.
     *
     * @return string The CSS filename.
     */
    #[Override]
    protected function getNameCss(): string
    {
        return 'pwd-renew.css';
    }

    /**
     * Returns additional JavaScript scripts required for the reset password page.
     *
     * @return string The HTML script tags for additional JavaScript resources.
     */
    #[Override]
    protected function getAdditionalScripts(): string
    {
        return '<script src="scripts/reset-password.js"></script>';
    }

    /**
     * Returns additional HTML headers for the reset password page.
     *
     * These include meta tags for SEO, authorship, and Open Graph (OG) integration
     * for Facebook, LinkedIn, and Instagram.
     *
     * @return string The HTML string containing additional meta headers.
     */
    #[Override]
    protected function getAdditionalHeaders(): string
    {
        return '<meta name="description" content="Page de réinitialisation du mot de passe de SAE Manager">
                <meta name="keywords" content="SAE Manager, Réinitialisation, Mot de passe">
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
