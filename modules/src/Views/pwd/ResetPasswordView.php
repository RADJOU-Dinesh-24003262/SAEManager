<?php

namespace Views\pwd;

use Utilis\SessionService;
use Views\AbstractView;

/**
 * Class ResetPasswordView

 * @package src

 * @subpackage pwd

 * @author Benhafessa Alexandre, Dargentolle Francois, Edelstein William, Griguer Nathan, Radjou Dinesh

 * This class represents the view for the "reset password" page of the application.
 * It extends the AbstractView class and provides specific implementations
 * for rendering the reset password page, including handling error messages.
 */
class ResetPasswordView extends AbstractView
{
    /**
     * The path of the HTML code to display for this view.
     *
     * @var string
     */
    private const TEMPLATE_HTML = __DIR__ . '/reset-password.html';

    /**
     * The constructor of the class, will use the constructor of the parent class AbstractView.
     * Also fills the $data variable with: token => $token, email => $email, variables given in parametters.
     *
     * @param string $token The token the user is assigned to reset their password.
     * @param string $email The email the user filled the reset password field with.

     * @return void Creates the instance of the class.
     */
    public function __construct(string $token, string $email)
    {
        $data = [
            'errors' => SessionService::getFlash('errors', []),
            'token' => $token,
            'email' => $email
        ];
        parent::__construct($data);
    }

    /**
     * Returns the path to the HTML template file.
     *
     * @return string
     */
    protected function templatePath(): string
    {
        return self::TEMPLATE_HTML;
    }

    /**
     * Returns an associative array of keys and values to be used in the HTML template.
     *
     * This method retrieves error messages, the token and the email of the user
     * and prepares them for rendering in the template.
     *
     * @return array An associative array
     */
    protected function templateKeys(): array
    {
        return [
            'ERROR_MESSAGES' => $this->renderErrorMessages($this->data['errors']),
            'TOKEN' => htmlspecialchars($this->data['token']),
            'EMAIL_DISPLAY' => htmlspecialchars($this->maskEmail($this->data['email']))
        ];
    }

    /**
     * Returns a masked version of the email
     *
     * This method replaces all but the first, last and arount dots characters with '*'
     * for security.
     *
     * Ex: jean.dupont@etu.univ-amu.fr → j***n.d***t@etu.univ-amu.fr
     *
     * @return string the hidden version of the email.
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

    /**
     * Returns the HTML to display and error message for the user.

     * If $error contains an error message, the method prepares a display for it and returns it.
     * Otherwise the method returns an empty string
     *
     * @return string the HTML string to be displayed, or an empty string if nothing is to be displayed
     */
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

    /**
     * Returns the name of the page 'Password Renew - SAEManager' or
     * be used in some cases like displaying it by some isolated texts.

     * @return string the name of the project 'Password Renew - SAEManager'.
     */
    protected function getPageTitle(): string
    {
        return 'Password Renew - SAEManager';
    }

    /**
     * Returns the name of the CSS file associated with the view.
     *
     * This method should be implemented by subclasses to specify the CSS file
     * that should be included in the HTML header for styling the page.
     *
     * @return string The name of the CSS file.
     */
    protected function getNameCss(): string
    {
        return 'pwd-renew.css';
    }

    /**
     * Returns additional HTML headers for the Login page.
     *
     * @return string The additional HTML headers.
     */
    protected function getAdditionalScripts(): string
    {
        return '<script src="scripts/reset-password.js"></script>';
    }
    /**
     * Returns additional HTML headers for the Reset Password page.
     *
     * @return string The additional HTML headers.
     */
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
