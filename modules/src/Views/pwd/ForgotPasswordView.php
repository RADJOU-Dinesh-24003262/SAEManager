<?php

namespace Views\pwd;

use Utilis\SessionService;
use Views\AbstractView;

/**
 * Class ForgotPasswordView
 *
 * @package src

 * @subpackage pwd

 * @author Benhafessa Alexandre, Dargentolle Francois, Edelstein William, Griguer Nathan, Radjou Dinesh
 *
 * This class represents the view for the "forgot password" page of the application.
 * It extends the AbstractView class and provides specific implementations
 * for rendering the forgot password page, including handling error and success messages.
 */
class ForgotPasswordView extends AbstractView
{
    //Constant for of the form
    /**
     * The field for the user to enter their email.
     *
     * @var string
     */
    public const FIELD_EMAIL = 'email';

    /**
     * The path of the HTML code to display for this view.
     *
     * @var string
     */
    private const TEMPLATE_HTML = __DIR__ . '/forgot-password.html';

    /**
     * The constructor of the class, will use the constructor of the parent class AbstractView.
     * Also fills the $data variable with success and errors data.

     * @return void Creates the instance of the class.
     */
    public function __construct()
    {
        $data = [
            'errors' => SessionService::getFlash('errors', []),
            'success' => SessionService::getFlash('success', '')

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
     * This method retrieves error messages and success messages from the session
     * and prepares them for rendering in the template.
     *
     * @return array An associative array with keys for error and success messages.
     */
    protected function templateKeys(): array
    {
        $errors = $this->data['errors'];
        $success = $this->data['success'];

        return [
            // Error and success messages
            'ERROR_MESSAGES' => $this->renderErrorMessages($errors),
            'SUCCESS_MESSAGE' => $this->renderSuccessMessage($success),
        ];
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
     * Returns the HTML to display and success message for the user.

     * If $success contains a success message, the method prepares a display for it and returns it.
     * Otherwise the method returns an empty string
     *
     * @return string the HTML string to be displayed, or an empty string if nothing is to be displayed
     */
    private function renderSuccessMessage(string $success): string
    {
        if (empty($success)) {
            return '';
        }

        return '<div class="alert alert-success">' . htmlspecialchars($success) . '</div>';
    }

    /**
     * Returns the name of the page 'Password Forgot - SAEManager' or
     * be used in some cases like displaying it by some isolated texts.

     * @return string the name of the project 'Password Forgot - SAEManager'.
     */
    protected function getPageTitle(): string
    {
        return 'Password Forgot - SAEManager';
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
        return 'forgot-password.css';
    }
    /**
     * Returns additional HTML headers for the forgot password page.
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
