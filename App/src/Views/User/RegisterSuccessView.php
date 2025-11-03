<?php

namespace Views\User;

use Models\User\Student;
use Core\AbstractView;
use Models\User\User;

/**
 * Class RegisterSuccessView
 *
 * View for the registration success page.
 * Displays information about the registered user.
 *
 * @category   View
 * @package    Src
 * @subpackage Views/User
 * @author     Alexandre Benhafessa <alexandre.benhafessa@etu.univ-amu.fr>
 * @author     François Dargentolle <francois.dargentolle@etu.univ-amu.fr>
 * @author     William Edelstein <william.edelstein@etu.univ-amu.fr>
 * @author     Nathan Griguer <nathan.griguer@etu.univ-amu.fr>
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>

 * @license MIT License https://opensource.org/licenses/MIT

 * @link https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class RegisterSuccessView extends AbstractView
{
    /**
     * Path to the HTML template file.
     *
     * @var string
     */
    private const TEMPLATE_HTML = __DIR__ . '/register-success.html';

    /**
     * Name of the CSS file for this view.
     *
     * @var string
     */
    private const CSS_REGISTER_SUCCESS = 'register-success.css';

    /**
     * The user that has just registered.
     *
     * @var User
     */
    private User $user;

    /**
     * Constructor.
     *
     * @param User $user The user who just registered.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
        parent::__construct();
    }

    /**
     * Returns the path to the HTML template.
     *
     * @return string
     */
    protected function templatePath(): string
    {
        return self::TEMPLATE_HTML;
    }

    /**
     * Returns keys and values to inject in the template.
     *
     * @return array<string, string>
     */
    protected function templateKeys(): array
    {
        return [
            'USER_FULL_NAME'  => $this->user->getFullName(),
            'USER_EMAIL'      => $this->user->getEmail(),
            'USER_TYPE_LABEL' => $this->getUserTypeLabel(),
            'ACADEMIC_INFO'   => $this->getAcademicInfo(),
        ];
    }

    /**
     * Returns the user type label in French.
     *
     * @return string
     */
    private function getUserTypeLabel(): string
    {
        switch ($this->user->getUserType()) {
            case 'student':
                return 'Étudiant';
            case 'professor':
                return 'Responsable SAE';
            case 'client':
                return 'Partenaire entreprise';
            default:
                return 'Utilisateur';
        }
    }

    /**
     * Returns academic info as an HTML div or empty string if not a student.
     *
     * @return string
     */
    private function getAcademicInfo(): string
    {
        if (!$this->user->isStudent() && $this->user instanceof Student) {
            /*
            * @var Student $student
            */
            $student = $this->user;


            $year    = $student->getYear();
            $parcours = $student->getParcours() ? $student->getParcours() : null;
            $td      = $student->getTd();
            $tp      = $student->getTp();

            $info = '<div class="academic-info">';
            $info .= '<h4>Informations académiques</h4>';
            $info .= "<p><strong>Année :</strong> BUT $year</p>";

            if ($parcours !== null) {
                $info .= "<p><strong>Parcours :</strong> $parcours</p>";
            }

            $info .= "<p><strong>Groupe TD :</strong> $td</p>";
            $info .= "<p><strong>Groupe TP :</strong> $tp</p>";
            $info .= '</div>';

            return $info;
        }

        return '';
    }

    /**
     * Returns the page title.
     *
     * @return string
     */
    protected function getPageTitle(): string
    {
        return 'Inscription réussie - SAEManager';
    }

    /**
     * Returns the CSS filename.
     *
     * @return string
     */
    protected function getNameCss(): string
    {
        return self::CSS_REGISTER_SUCCESS;
    }

    /**
     * Returns additional HTML headers for the page.
     *
     * @return string
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
