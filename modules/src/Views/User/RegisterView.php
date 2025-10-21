<?php

namespace Views\User;

use DateTime;
use Views\AbstractView;
use Utilis\SessionService;

/**
 * Class RegisterView

 * @package src

 * @subpackage User

 * @author Benhafessa Alexandre, Dargentolle Francois, Edelstein William, Griguer Nathan, Radjou Dinesh

 * This class represents the view for the registration page of the application.
 * It extends the AbstractView class and provides specific implementations
 * for rendering the registration page, including handling error messages.
 */
class RegisterView extends AbstractView
{
    // Constant for form field names

    /**
     * The identification String of the user. Will be used as a primary key to recognize the user.
     *
     * @var string
     */
    public const FIELD_ID = 'id';
    /**
     * The first name of the user. Used for the user interface.
     *
     * @var string
     */
    public const FIELD_FNAME = 'fname';
    /**
     * The last name of the user. Used for the user interface
     *
     * @var string
     */
    public const FIELD_LNAME = 'lname';
    /**
     * The gender of the user. Used for the user interface
     *
     * @var string
     */
    public const FIELD_GENDER = 'gender';
    /**
     * The user type. It might be either a student, an SAE administrator or a client (subject maker of the SAEs)
     *
     * @var string
     */
    public const FIELD_USER_TYPE = 'user_type';
    /**
     * The email of the user. Will be used to contact them,
     * to identify them, to help recover password and some more usages.
     *
     * @var string
     */
    public const FIELD_EMAIL = 'email';
    /**
     * The password of the user. Used to allow them to login, and secure their accounts.
     *
     * @var string
     */
    public const FIELD_PASSWORD = 'pwd';
    /**
     * Field to make sure the user typed his password right, they have to type it twice.
     *
     * @var string
     */
    public const FIELD_PASSWORD_CONFIRM = 'pwdverif';
    /**
     * The phone number of the user. used to secure the site, to contact them and identify an account.
     *
     * @var string
     */
    public const FIELD_PHONE = 'tel';
    /**
     * The date of birth of a user. Used to identify them.
     *
     * @var string
     */
    public const FIELD_DOB = 'dob';
    /**
     * The city of studying of the user. Used to locate and search users efficiently.
     *
     * @var string
     */
    public const FIELD_CITY = 'city';
    /**
     * The study year of the undergraduate. Used to locate and search and sort users efficiently.
     *
     * @var string
     */
    public const FIELD_YEAR = 'year';
    /**
     * The major of the student. Used to locate search and sort users efficiently.
     *
     * @var string
     */
    public const FIELD_PARCOURS = 'parcours';
    /**
     * The field to give the sub-group in the promotion of the user (if their is any).
     *
     * @var string
     */
    public const FIELD_TD = 'td';
    /**
     * The field to give the sub-sub-group in the promotion of the user (if their is any).
     *
     * @var string
     */
    public const FIELD_TP = 'tp';
    /**
     * The variable that gives weather the user has accepted the terms and conditions.
     *
     * @var string
     */
    public const FIELD_TERMS = 'terms';

    /**
     * The path of the html template with the form
     *
     * @var string
     */
    private const TEMPLATE_HTML = __DIR__ . '/register.html';

    /**
     * The constructor of the class, will use the constructor of the parent class AbstractView
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

        return [
            // Error messages
            'ERROR_MESSAGES' => $this->renderErrorMessages($errors),

            // Max birth date for 16 years old
            'MAX_BIRTH_DATE' => date('Y-m-d', strtotime('-16 years'))
        ];
    }

    /**
     * Renders an error message given in parrameters.
     *
     * This method orchestrates the rendering of the error messages of the page.
     *
     * @return string The html to be displayed.
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
     * Returns the name of the page 'Inscription - SAEManager' or
     * be used in some cases like displaying it by some isolated texts.

     * @return string the name of the project 'Inscription - SAEManager'.
     */
    protected function getPageTitle(): string
    {
        return 'Inscription - SAEManager';
    }

    /**
     * Returns additional scripts to be included before closing a body tag.
     *
     * @return string The additional scripts.
     */
    protected function getAdditionalScripts(): string
    {
        return '<script src="/scripts/register.js"></script>';
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
        return 'register.css';
    }
    /**
     * Returns additional HTML headers for the Register page.
     *
     * @return string The additional HTML headers.
     */
    protected function getAdditionalHeaders(): string
    {
        return '<meta name="description" content="Page d\'inscription de SAEManager">
                <meta name="keywords" content="SAEManager, Inscription">
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
