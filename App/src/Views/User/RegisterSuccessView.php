<?php

namespace Views\User;

use Core\Views\AbstractView;
use Models\Entity\User\Student;
use Models\Entity\User\User;
use Override;

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
    #[Override]
    protected function templatePath(): string
    {
        return self::TEMPLATE_HTML;
    }

    /**
     * Returns keys and values to inject in the template.
     *
     * @return array<string, string>
     */
    #[Override]
    protected function templateKeys(): array
    {
        return [
            'USER_FULL_NAME'  => $this->user->getFullName(),
            'USER_EMAIL'      => $this->user->getEmail(),
            'USER_TYPE_LABEL' => $this->user->getRoleLabel(),
            'ACADEMIC_INFO'   => $this->getAcademicInfo(),
        ];
    }



    /**
     * Returns extra user info as an HTML div or empty string if none.
     *
     * @return string
     */
    private function getAcademicInfo(): string
    {
        $metaInfo = $this->user->getDashboardMetaInfo();

        $info = '<div class="academic-info">';
        $info .= '<h4>Informations complémentaires</h4>';

        foreach ($metaInfo as $label => $value) {
            $info .= "<p><strong>" . $label . " :</strong> " . (string) $value . "</p>";
        }

        $info .= '</div>';
        return $info;
    }

    /**
     * Returns the page title.
     *
     * @return string
     */
    #[Override]
    protected function getPageTitle(): string
    {
        return 'Inscription réussie - SAE Manager';
    }

    /**
     * Returns the CSS filename.
     *
     * @return string
     */
    #[Override]
    protected function getNameCss(): string
    {
        return self::CSS_REGISTER_SUCCESS;
    }

    /**
     * Returns additional HTML headers for the page.
     *
     * @return string
     */
    #[Override]
    protected function getAdditionalHeaders(): string
    {
        return '<meta name="description" content="Page de succès d\'inscription de SAE Manager">
                <meta name="keywords" content="SAE Manager, Inscription, Succès">
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
