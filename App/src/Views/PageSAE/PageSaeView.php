<?php

namespace Views\PageSAE;

use Models\Entity\SAE\SAESubject;
use Models\Entity\User\User;
use Override;
use Services\FileService;
use Views\BaseSaeView;
use Core\Utilis\SessionService;
use Parsedown;
use Models\Entity\SAE\SAEGroup;

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
     * @var array<int, array{
     *      group: SAEGroup,
     *      students: array<int, array{
     *          student_id: string,
     *          amu_id: string,
     *          year: string,
     *          td: string,
     *          tp: string,
     *          first_name: string,
     *          last_name: string,
     *          email: string
     *      }>
     * }>
     */
    protected array $groups;
    /**
     * @var array<string, mixed>
     */
    protected array $responsibleProf;

    /**
     * @var array<int, array<string, mixed>>
     */
    protected array $allProfessors;

    /**
     * @var array<string, mixed>|null
     */
    protected ?array $client;

    /**
     * Constructs a new PageSaeView instance.
     * @param SAESubject                $subject              The SAE subject details.
     * @param array<int, mixed>         $groups               The groups associated with the SAE.
     * @param array<string, mixed>|null $responsibleProfessor The responsible professor's details,
     *                                                        or null if none.
     * @param array<int, mixed>|null    $allProfessors        All professors associated with the SAE.
     * @param array<mixed>|null         $client               The client's details, or null if none.
     * @param User                      $user                 The current user.
    */
    public function __construct(
        SAESubject $subject,
        array $groups,
        ?array $responsibleProfessor,
        ?array $allProfessors,
        ?array $client,
        User $user
    ) {
        parent::__construct($subject, $user);

        $this->groups = $groups;
        $this->responsibleProf = $responsibleProfessor ?? [];
        $this->allProfessors = $allProfessors ?? [];
        $this->client = $client;
    }

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
    * Returns an associative array of keys and values to be used in the HTML template.
    *
    * This method retrieves error messages and success messages from the session
    * and prepares them for rendering in the template.
    *
    * @return array<string, int|string|null> An associative array.
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
        $user = $this->user;
        $content = '';

        // 1. Responsible Professor
        if (!empty($this->responsibleProf)) {
            $prof = $this->responsibleProf;
            $name = $prof['first_name'] . ' ' . $prof['last_name'];
            $email = $prof['email'];
            $content .= '<div class="contact-section"><h3>🎓 Responsable de la SAE</h3>';
            $content .= '<p>' . $name . ' - <a href="mailto:' . $email . '">' . $email . '</a></p></div>';
        }

        // 2 Associated Professors
        $associatedProfsToDisplay = [];

        if ($user->isProfessor() || $user->isClient()) {
            $associatedProfsToDisplay = $this->allProfessors;
        } elseif (!empty($this->groups)) {
            foreach ($this->groups as $groupData) {
                $profId = $groupData['group']->getProfessorId();
                foreach ($this->allProfessors as $p) {
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
                $content .= '<div class="contact-section"><h3>👨‍🏫 Professeur de votre groupe</h3><ul>';
            } else {
                $content .= '<div class="contact-section"><h3>👨‍🏫 Professeur(s) Associé(s)</h3><ul>';
            }
            foreach ($associatedProfsToDisplay as $prof) {
                $pName = $prof['first_name'] . ' ' . $prof['last_name'];
                $pEmail = $prof['email'];
                $content .= '<li>' . $pName . ' - <a href="mailto:' . $pEmail . '">' . $pEmail . '</a></li>';
            }
            $content .= '</ul></div>';
        }

        // 3. Client (if user is not the client)
        if (!$user->isClient() && !empty($this->client)) {
            $client = $this->client;
            $name = $client['first_name'] . ' ' . $client['last_name'];
            $email = $client['email'];
            $org = !empty($client['organisation']) ? ' (' . $client['organisation'] . ')' : '';
            $content .= '<div class="contact-section"><h3>🏢 Client</h3>';
            $content .= '<p>' . $name . $org . ' - <a href="mailto:' . $email . '">' . $email . '</a></p></div>';
        }

        // 4. Groups (Students).
        if ($user->isStudent()) {
            // Students see their own group members.
            if (!empty($this->groups)) {
                // Assuming only one group is returned for the student due to logic in SAE model.
                foreach ($this->groups as $groupData) {
                    $groupName = 'Groupe ' . $groupData['group']->getSaeGroupId();

                    $content .= '<div class="contact-section"><h3>👥 ' . $groupName . '</h3><ul>';

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
            if (!empty($this->groups)) {
                $content .= '<div class="contact-section"><h3>👥 Groupes d\'étudiants</h3>';

                foreach ($this->groups as $groupData) {
                    $groupName = 'Groupe ' . $groupData['group']->getSaeGroupId();

                    $content .= '<h4>' . $groupName . '</h4><ul>';

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
        $content = '<p>Nom de la SAE : ' . $this->subject->getSubjectName() . '</p>';
        $content .= '<p>Début de la SAE : ' . $this->subject->getBeginDate() . '</p>';
        $content .= '<p>Fin de la SAE : ' . $this->subject->getEndDate() . '</p>';

        $profRes = $this->responsibleProf;
        $allProfs = $this->allProfessors;

        $profs = [];

        if ($this->user->isProfessor() || $this->user->isClient()) {
            $profs = $allProfs;
        } else {
            if (!empty($this->groups)) {
                foreach ($this->groups as $groupData) {
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

        $client = $this->client ?: 'Pas de client';

        $profResLastName = $profRes['last_name'] ?? 'Inconnu';
        $profResFirstName = $profRes['first_name'] ?? 'Inconnu';

        $profLastName = [];
        $profFirstName = [];
        foreach ($profs as $prof) {
            $profLastName[] = $prof['last_name'];
            $profFirstName[] = $prof['first_name'];
        }

        $clientLastName = $client['last_name'] ?? 'Inconnu';
        $clientFirstName = $client['first_name'] ?? 'Inconnu';

        $content .= '<p> Le Responsable de la ressource est ' . $profResLastName . ' ' . $profResFirstName . '.</p>';

        if (empty($profs)) {
            $content .= '<p> Aucun professeur associé à la ressource.</p>';
            return $content;
        } else {
            if ($this->user->isStudent()) {
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

        $filePath = $this->subject->getFilePath() ?? '';

        $content .= '<h3>Description de la SAE :</h3>';

        try {
            $description = FileService::getSaeDescription($filePath);
        } catch (\Exception $e) {
            $description = '';
        }

        if ($description == '') {
            $content .= '<p>Aucune description disponible.</p>';
        } else {
            $parsedown = new Parsedown();
            $content .= '<article><div class="sae-subject-file">' . $parsedown->text($description) . '</div></article>';
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
        return 'SAE ' . $this->subject->getSubjectName() . ' - SAE Manager';
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
