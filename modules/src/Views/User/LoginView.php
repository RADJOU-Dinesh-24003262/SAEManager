<?php

namespace Views\User;

use Views\AbstractView;
use Utilis\SessionService;

/**

 * The view to display the login page. It extends the AbstractView abstract class.

 *

 * It implements the methods of the AbstractView extended class and behaves as a login page

 *

 * @package     src

 * @subpackage  User

 * @author      Benhafessa Alexandre, Dargentolle Francois, Edelstein William, Griguer Nathan, Radjou Dinesh

 */
class LoginView extends AbstractView
{
    /**
     * The path of the HTML code to display for this view.
     * @var string
     */
    private const TEMPLATE_HTML = __DIR__ . '/loginview.html';

    /**
     * Returns the path to the HTML template file.
     *
     * @return string
     */
    protected function templatePath(): string
    {
        return self::TEMPLATE_HTML;
    }
    /** Returns an associative array of keys and values to be used in the HTML template.
     *
     * This method retrieves error messages and success messages from the session
     * and prepares them for rendering in the template.
     *
     * @return array An associative array with keys for error and success messages.
     */
    protected function templateKeys(): array
    {
        $errors = $this->data['errors'];

        return [
            // Error messages
            'ERROR_MESSAGES' => $this->renderErrorMessages($errors)
        ];
    }
    /**

     * The constructor of the class, will use the constructor of the parent class AbstractView.
     * Also fills the $data variable with potential error messages.

     *

     * @return void Creates the instance of the class.

     */
    public function __construct()
    {
        $data = [
            'errors' => SessionService::getFlash('errors', []),
        ];
        parent::__construct($data);
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

        $html = '<div class="alert alert-error"><ul>';
        foreach ($errors as $error) {
            $html .= '<li>' . $error . '</li>';
        }
        $html .= '</ul></div>';

        return $html;
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
        return 'style.css';
    }
    /** Returns additional HTML headers for the Login page.
     *
     * @return string The additional HTML headers.
     */
    protected function getAdditionalHeaders(): string
    {
        return '<meta name="description" content="Page de connexion de SAEManager">
                <meta name="keywords" content="SAEManager, Connexion">
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