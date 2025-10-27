<?php

namespace Views\User;

use DateTime;
use Core\AbstractView;
use Core\Utilis\SessionService;

/**
 * Class RegisterView
 *
 * This class represents the view for the registration page of the application.
 * It extends the AbstractView class and provides specific implementations
 * for rendering the registration page, including handling error messages.
 *
 * @category View
 * @package  Src
 * @subpackage Views\User
 *
 * @author   Alexandre Benhafessa <alexandre.benhafessa@etu.univ-amu.fr>
 * @author   François Dargentolle <francois.dargentolle@etu.univ-amu.fr>
 * @author   William Edelstein <william.edelstein@etu.univ-amu.fr>
 * @author   Nathan Griguer <nathan.griguer@etu.univ-amu.fr>
 * @author   Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 *
 * @license  MIT License https://opensource.org/licenses/MIT
 * @link     https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class RegisterView extends AbstractView
{
    // -------------------------------------------------------------------------
    // Constants
    // -------------------------------------------------------------------------

    /** @var string The identification String of the user. */
    public const FIELD_ID = 'id';

    /** @var string The first name of the user. */
    public const FIELD_FNAME = 'fname';

    /** @var string The last name of the user. */
    public const FIELD_LNAME = 'lname';
    /**
     * The user type. It might be either a student, an SAE administrator or a client (subject maker of the SAEs)
     * @var string
     */
    public const FIELD_USER_TYPE = 'user_type';

    /** @var string The email of the user. */
    public const FIELD_EMAIL = 'email';

    /** @var string The password of the user. */
    public const FIELD_PASSWORD = 'pwd';

    /** @var string The password confirmation field. */
    public const FIELD_PASSWORD_CONFIRM = 'pwdverif';

    /** @var string The phone number of the user. */
    public const FIELD_PHONE = 'tel';
    /**
     * The study year of the undergraduate. Used to locate and search and sort users efficiently.
     * @var string
     */
    public const FIELD_YEAR = 'year';

    /** @var string The student's major or specialization. */
    public const FIELD_PARCOURS = 'parcours';

    /** @var string The TD (tutorial group) of the user. */
    public const FIELD_TD = 'td';

    /** @var string The TP (lab group) of the user. */
    public const FIELD_TP = 'tp';

    /** @var string Whether the user accepted the terms and conditions. */
    public const FIELD_TERMS = 'terms';

    /** @var string The path to the HTML template file. */
    private const TEMPLATE_HTML = __DIR__ . '/register.html';

    // -------------------------------------------------------------------------
    // Constructor
    // -------------------------------------------------------------------------

    /**
     * RegisterView constructor.
     *
     * Initializes the view with any error messages stored in the session.
     */
    public function __construct()
    {
        $data = [
            'errors' => SessionService::getFlash('errors', []),
        ];
        parent::__construct($data);
    }

    // -------------------------------------------------------------------------
    // Template Rendering
    // -------------------------------------------------------------------------

    /**
     * Returns the path to the HTML template file.
     *
     * @return string The full path to the HTML template.
     */
    protected function templatePath(): string
    {
        return self::TEMPLATE_HTML;
    }

    /**
     * Returns an associative array of keys and values to be used in the HTML template.
     *
     * @return array<string, string> The keys and corresponding rendered values.
     */
    protected function templateKeys(): array
    {
        $errors = $this->data['errors'];

        return [
            // Error messages.
            'ERROR_MESSAGES' => $this->renderErrorMessages($errors)
        ];
    }

    /**
     * Renders the list of error messages into an HTML block.
     *
     * @param array<int, string> $errors The list of error messages.
     *
     * @return string The HTML representation of the errors, or an empty string.
     */
    private function renderErrorMessages(array $errors): string
    {
        if (empty($errors)) {
            return '';
        }

        $html = '<section role="alert" aria-live="assertive" class="alert alert-error"><ul>';
        foreach ($errors as $error) {
            $html .= '<li>' . htmlspecialchars($error, ENT_QUOTES, 'UTF-8') . '</li>';
        }
        $html .= '</ul></section>';

        return $html;
    }

    // -------------------------------------------------------------------------
    // Metadata and Assets
    // -------------------------------------------------------------------------

    /**
     * Returns the title of the registration page.
     *
     * @return string The page title.
     */
    protected function getPageTitle(): string
    {
        return 'Inscription - SAEManager';
    }

    /**
     * Returns additional scripts to be included before the closing body tag.
     *
     * @return string The HTML script tags.
     */
    protected function getAdditionalScripts(): string
    {
        return '<script src="/scripts/register.js"></script>';
    }

    /**
     * Returns the name of the CSS file associated with this view.
     *
     * @return string The CSS filename.
     */
    protected function getNameCss(): string
    {
        return 'register.css';
    }

    /**
     * Returns additional HTML headers for the Register page.
     *
     * @return string The HTML meta tags.
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
