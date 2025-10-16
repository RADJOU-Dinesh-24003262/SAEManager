<?php
namespace Views\User;

use Views\AbstractView;
use Models\User\User;

/**
 
 * Class RegisterSuccessView
 
 * @package     src
 
 * @subpackage  User 
 
 * @author      Benhafessa Alexandre, Dargentolle Francois, Edelstein William, Griguer Nathan, Radjou Dinesh
 
 * This class represents the view for the registration success page of the application.
 * It extends the AbstractView class and provides specific implementations
 * for rendering the registration success page, including displaying user information.
 
 */
class RegisterSuccessView extends AbstractView
{
    /**
     * The path of the HTML code to display for this view.
     * @var string
     */
    private const TEMPLATE_HTML = __DIR__ . '/register-success.html';
    /**
     * The path of the CSS code for the HTML code to display for this view
     * @var string
     */
    private const CSS_REGISTER_SUCCESS = 'register-success.css';
    /**
     * The variable that stores the data of the user that just logged in
     * @var User
     */
    private User $user;

    /**

     * The constructor of the class, will use the constructor of the parent class AbstractView.
     * Takes the user in parametter and stores it into the user variable of this class.

     *
     
     * @param User $user the user that just logged in.

     * @return void Creates the instance of the class.

     */
    public function __construct(User $user)
    {
        $this->user = $user;
        parent::__construct();
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
     * This method retrieves the first and last name of the user, their email, user type and academic infos
     * and prepares them for rendering in the template.
     *
     * @return array An associative array with keys for error and success messages.
     */
    protected function templateKeys(): array
    {
        return [
            'USER_FULL_NAME' => $this->user->getFullName(),
            'USER_EMAIL' => $this->user->getEmail(),
            'USER_TYPE_LABEL' => $this->getUserTypeLabel(),
            'ACADEMIC_INFO' => $this->getAcademicInfo()
        ];
    }
    /** 
     * Returns the french user type label
     *
     * This method is used to turn the english user labels into the french user labels the user will see
     *
     * @return string The french version of the user type label. If it isn't found, returns 'Utilisateur'.
     */
    private function getUserTypeLabel(): string
    {
        error_log("User type: " . $this->user->getUserType(), 0, 'php_errors.log');
        switch ($this->user->getUserType()) {
            case 'student':
                return 'Étudiant';
            case 'professor':
                return 'Responsable SAE';
            case 'companies':
                return 'Partenaire entreprise';
            default:
                return 'Utilisateur';
        }
    }

    /** 
     * Returns a div make into the method to be displayed to the user, only if the user is a student, Returns an empty string otherwise.
     *
     * This method makes an html div with the academic year, major, sub group and sub-sub group. If the user isn't a student
     * this method returns an empty string.
     *
     * @return string The HTML div ready to be displayed.
     */
    private function getAcademicInfo(): string
    {
        if (!$this->user->isStudent()) {
            return '';
        }

        $info = '<div class="academic-info">';
        $info .= '<h4>Informations académiques</h4>';
        $info .= '<p><strong>Année :</strong> BUT ' . $this->user->getYear() . '</p>';
        
        if ($this->user->getParcours()) {
            $info .= '<p><strong>Parcours :</strong> ' . $this->user->getParcours() . '</p>';
        }

        $info .= '<p><strong>Groupe TD :</strong> ' . $this->user->getTd() . '</p>';
        $info .= '<p><strong>Groupe TP :</strong> ' . $this->user->getTp() . '</p>';
        $info .= '</div>';

        return $info;
    }

    /**

     * Returns the name of the page 'Inscription réussie - SAEManager' or be used in some cases like displaying it by some isolated texts.

     *

     * @return string the name of the project 'Inscription réussie - SAEManager'.

     */
    protected function getPageTitle(): string
    {
        return 'Inscription réussie - SAEManager';
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
        return self::CSS_REGISTER_SUCCESS;
    }
    /** Returns additional HTML headers for the Register Success page.
     *
     * @return string The additional HTML headers.
     */
    protected function getAdditionalHeaders(): string
    {
        return '<meta name="description" content="Page de succès d\'inscription de SAEManager">
                <meta name="keywords" content="SAEManager, Inscription, Succès">
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