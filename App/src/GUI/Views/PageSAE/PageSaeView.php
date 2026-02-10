<?php

namespace App\GUI\Views\PageSAE;

use Override;
use App\GUI\Views\BaseSaeView;
use App\Infrastructure\Service\SessionService;
use App\Domain\SAE\Sae;
use Parsedown;

use function Parsica\Parsica\append;

/**
 * Class PageSaeView
 * This class represents the view for the page of the application where we will see the SAE .
 * It extends the BaseSaeView class and provides specific implementations for rendering the SAE page.

 * @category View

 * @package Src

 * @subpackage Views/PageSAE

 * @author Alexandre Benhafessa <alexandre.benhafessa@etu.univ-amu.fr>
 * @author François Dargentolle <francois.dargentolle@etu.univ-amu.fr>
 * @author William Edelstein <william.edelstein@etu.univ-amu.fr>
 * @author Nathan Griguer <nathan.griguer@etu.univ-amu.fr>
 * @author Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>

 * @license MIT License https://opensource.org/licenses/MIT

 * @link https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class PageSaeView extends BaseSaeView
{
    /**
     * The path of the HTML code to display for this view.
     *
     * @var string
     */
    private const TEMPLATE_HTML = __DIR__ . '/pageSae.html';


    /**
     * Returns the path to the HTML template file.
     *
     * @return string
     */
    #[Override]
    protected function templatePath(): string
    {
        return self::TEMPLATE_HTML;
    }

    /**
     * Returns an empty array. Implemented from the parent class.
     *
     * This method returns an empty array.
     *
     * @return array<string, string> An empty array
     */
    #[Override]
    protected function templateKeys(): array
    {
        $errors = $this->data['errors'] ?? [];

        return array_merge(
            $this->getCommonSaeTemplateKeys(),
            [
                'ERROR_MESSAGES' => $this->renderErrorMessages($errors),
                'SUCCESS_MESSAGE' => $this->renderSuccessMessage(),
                'SAE_CONTENT' => $this->getDescriptionSae(),
                'SAE_CONTACTS' => $this->getContactsSae()
            ]
        );
    }

    /**
     * Generates the contacts section.
     *
     * @return string The HTML for the contacts.
     */
    protected function getContactsSae(): string
    {
        $user = $this->data['user'];
        $saeData = $this->data['sae'];
        $content = '';

        // 1. Responsible Professor
        if (!empty($saeData['responsible_professor'])) {
            $prof = $saeData['responsible_professor'];
            $name = $prof['first_name'] . ' ' . $prof['last_name'];
            $email = $prof['email'];
            $content .= '<div class="contact-section"><h5>🎓 Responsable de la SAE</h5>';
            $content .= '<p>' . $name . ' - <a href="mailto:' . $email . '">' . $email . '</a></p></div>';
        }

        // 2 Associated Professors
        $allProfs = $saeData['all_professors'];
        $associatedProfsToDisplay = [];

        if ($user->isProfessor() || $user->isClient()) {
            $associatedProfsToDisplay = $allProfs;
        } elseif (!empty($saeData['groups'])) {
            foreach ($saeData['groups'] as $groupData) {
                $profId = $groupData['group']->getProfessorId();
                foreach ($allProfs as $p) {
                    if ($p['user_id'] == $profId) {
                        $associatedProfsToDisplay[] = $p;
                        break;
                    }
                }
            }
            $associatedProfsToDisplay = array_unique($associatedProfsToDisplay, SORT_REGULAR);
        }

        if (!empty($associatedProfsToDisplay)) {
            if ($user->isStudent()) {
                $content .= '<div class="contact-section"><h5>👨‍🏫 Professeur de votre groupe</h5><ul>';
            } else {
                $content .= '<div class="contact-section"><h5>👨‍🏫 Professeur(s) Associé(s)</h5><ul>';
            }
            foreach ($associatedProfsToDisplay as $prof) {
                $pName = $prof['first_name'] . ' ' . $prof['last_name'];
                $pEmail = $prof['email'];
                $content .= '<li>' . $pName . ' - <a href="mailto:' . $pEmail . '">' . $pEmail . '</a></li>';
            }
            $content .= '</ul></div>';
        }

        // 3. Client (if user is not the client)
        if (!$user->isClient() && !empty($saeData['client'])) {
            $client = $saeData['client'];
            $name = $client['first_name'] . ' ' . $client['last_name'];
            $email = $client['email'];
            $org = !empty($client['organisation']) ? ' (' . $client['organisation'] . ')' : '';
            $content .= '<div class="contact-section"><h5>🏢 Client</h5>';
            $content .= '<p>' . $name . $org . ' - <a href="mailto:' . $email . '">' . $email . '</a></p></div>';
        }

        // 4. Groups (Students).
        if ($user->isStudent()) {
            // Students see their own group members.
            if (!empty($saeData['groups'])) {
                // Assuming only one group is returned for the student due to logic in SAE model.
                foreach ($saeData['groups'] as $groupData) {
                    $groupName = 'Groupe ' . $groupData['group']->getId();

                    $content .= '<div class="contact-section"><h5>👥 ' . $groupName . '</h5><ul>';

                    foreach ($groupData['students'] as $student) {
                        // Don't show the current user in the list? Optional. Showing everyone is fine.
                        $sName = $student['first_name'] . ' ' . $student['last_name'];

                        $sEmail = $student['email'];

                        $content .= '<li>' . $sName . ' - <a href="mailto:' . $sEmail . '">' . $sEmail . '</a></li>';
                    }

                    $content .= '</ul></div>';
                }
            }
        } else {
            // Clients see all groups.
            if (!empty($saeData['groups'])) {
                $content .= '<div class="contact-section"><h5>👥 Groupes d\'étudiants</h5>';

                foreach ($saeData['groups'] as $groupData) {
                    $groupName = 'Groupe ' . $groupData['group']->getId();

                    $content .= '<h6>' . $groupName . '</h6><ul>';

                    if (empty($groupData['students'])) {
                        $content .= '<li>Aucun étudiant.</li>';
                    } else {
                        foreach ($groupData['students'] as $student) {
                            $sName = $student['first_name'] . ' ' . $student['last_name'];

                            $sMail = $student['email'];

                            $content .= '<li>' . $sName . ' - <a href="mailto:' . $sMail . '">' . $sMail . '</a></li>';
                        }
                    }

                    $content .= '</ul>';
                }

                $content .= '</div>';
            } else {
                $content .= '<p>Aucun groupe assigné pour le moment.</p>';
            }
        }

        return $content;
    }

    /**
     * Generates the description of the SAE.
     *
     * This method constructs an HTML description of the SAE using its attributes.
     *
     * @return string The HTML description of the SAE.
     */
    protected function getDescriptionSae(): string
    {
        $content = '<p>Nom de la SAE : ' . $this->data['sae']['subject']->getName() . '</p>';
        $content .= '<p>Début de la SAE : ' . $this->data['sae']['subject']->getBeginDate() . '</p>';
        $content .= '<p>Fin de la SAE : ' . $this->data['sae']['subject']->getEndDate() . '</p>';

        $profRes = $this->data['sae']['responsible_professor'];
        $allProfs = $this->data['sae']['all_professors'];

        $profs = [];

        if ($this->data['user']->isProfessor() || $this->data['user']->isClient()) {
            $profs = $allProfs;
        } else {
            if (!empty($this->data['sae']['groups'])) {
                foreach ($this->data['sae']['groups'] as $groupData) {
                    $profId = $groupData['group']->getProfessorId();
                    foreach ($allProfs as $p) {
                        if ($p['user_id'] == $profId) {
                            $profs[] = $p;
                            break;
                        }
                    }
                }
            }
            $profs = array_unique($profs, SORT_REGULAR);
        }

        $client = $this->data['sae']['client'] ?: 'Pas de client';

        $profResLastName = isset($profRes['last_name']) ? $profRes['last_name'] : 'Inconnu';
        $profResFirstName = isset($profRes['first_name']) ? $profRes['first_name'] : 'Inconnu';

        $profLastName = [];
        $profFirstName = [];
        foreach ($profs as $prof) {
            $profLastName[] = isset($prof['last_name']) ? $prof['last_name'] : 'Inconnu';
            $profFirstName[] = isset($prof['first_name']) ? $prof['first_name'] : 'Inconnu';
        }

        $clientLastName = isset($client['last_name']) ? $client['last_name'] : 'Inconnu';
        $clientFirstName = isset($client['first_name']) ? $client['first_name'] : 'Inconnu';

        $content .= '<p> Le Responsable de la ressource est ' . $profResLastName . ' ' . $profResFirstName . '.</p>';

        if (empty($profs)) {
            $content .= '<p> Aucun professeur associé à la ressource.</p>';
            return $content;
        } else {
            if ($this->data['user']->isStudent()) {
                $content .= '<p> Le professeur de votre groupe est ';
            } else {
                $content .= '<p> Les professeurs associés à la ressource sont ';
            }
            foreach ($profs as $index => $prof) {
                $content .= $profLastName[$index] . ' ' . $profFirstName[$index];
                if ($index < count($profs) - 1) {
                    $content .= ', ';
                }
            }
            $content .= '.</p>';
        }
        $content .= '<p> Le client associé à cette SAE est ' . $clientLastName . ' ' . $clientFirstName .
            '.</p></article>';

        $filePath = $this->data['sae']['subject']->getDescriptionFilePath();

        $content .= '<h3>Description de la SAE :</h3>';

        if ($filePath) {
            $fullPath = __DIR__ . '/../../../../storage/sae_descriptions/' . $filePath;
            if (file_exists($fullPath)) {
                $parsedown = new Parsedown();
                $content .= '<article><div class="sae-subject-file">';
                $content .= $parsedown->text(file_get_contents($fullPath));
                $content .= '</div></article>';
            }
        }
        return $content;
    }

    /**
     * Returns the name of the page 'Page SAE - SAE Manager'
     * or be used in some cases like displaying it by some isolated texts.

     * @return string the name of the project 'Page SAE - SAE Manager'.
     */
    #[Override]
    protected function getPageTitle(): string
    {
        return 'SAE ' . $this->data['sae']['subject']->getName() . ' - SAE Manager';
    }

    /**
     * Returns the name of the CSS file associated with the view.
     *
     * This method should be implemented by subclasses to specify the CSS file
     * that should be included in the HTML header for styling the page.
     *
     * @return string The name of the CSS file.
     */
    #[Override]
    protected function getNameCss(): string
    {
        return 'page-sae.css';
    }
    /**
     * Returns additional HTML headers for the Sae page.
     *
     * @return string The additional HTML headers.
     */
    #[Override]
    protected function getAdditionalHeaders(): string
    {
        return '<meta name="description" content="Page SAE de SAE Manager">
                <meta name="keywords" content="SAE Manager, SAE">
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
                <meta property="og:type" content="website" />' ;
    }
}
