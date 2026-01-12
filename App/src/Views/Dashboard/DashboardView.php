<?php

namespace Views\Dashboard;

use Core\Views\AbstractView;
use Models\SAE\SAESubject;
use Models\User\Client;
use Models\User\Student;
use Models\User\User;
use Override;

/**
 * Class DashboardView
 *
 * Represents the view for the user dashboard page of SAE Manager.
 * This page displays personalized information about the connected user
 * (name, email, role, SAE list, etc.) and provides navigation elements
 * specific to their role (student, professor, or client).
 *
 * @category   View
 * @package    Src
 * @subpackage Views/Dashboard
 * @author     Alexandre Benhafessa <alexandre.benhafessa@etu.univ-amu.fr>
 * @author     François Dargentolle <francois.dargentolle@etu.univ-amu.fr>
 * @author     William Edelstein <william.edelstein@etu.univ-amu.fr>
 * @author     Nathan Griguer <nathan.griguer@etu.univ-amu.fr>
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class DashboardView extends AbstractView
{
    /**
     * Path to the HTML template file for the dashboard.
     *
     * @var string
     */
    private const TEMPLATE_HTML = __DIR__ . '/dashboard.html';

    /**
     * DashboardView constructor.
     *
     * Initializes the dashboard with user-specific data and flash messages.
     *
     * @param array<string, mixed> $data Data passed to the view. Must include a 'user' key.
     */
    public function __construct(array $data)
    {
        $data = [
            'user'    => $data['user'],
            'saes'    => $data['saes'],
            'sae'     => $data['sae']
        ];

        parent::__construct($data);
    }

    /**
     * Returns the path to the dashboard HTML template file.
     *
     * @return string The template path.
     */
    #[Override]
    protected function templatePath(): string
    {
        return self::TEMPLATE_HTML;
    }

    /**
     * Returns the list of keys and rendered values used in the HTML template.
     *
     * @return array<string, string> The list of template keys and values.
     */
    #[Override]
    protected function templateKeys(): array
    {
        /** @var array<int|string, string> $errors */
        $errors = $this->data['errors'] ?? [];
        /** @var User $user */
        $user   = $this->data['user'];
        /** @var array<SAESubject> $saes */
        $saes   = $this->data['saes'] ?? [];

        return [
            'ERROR_MESSAGES'   => $this->renderErrorMessages($errors),
            'SUCCESS_MESSAGE'  => $this->renderSuccessMessage(),
            'USER_NAME'        => $user->getFullName(),
            'USER_EMAIL'       => $user->getEmail(),
            'USER_TYPE_CLASS'  => $this->getUserTypeLabel($user),
            'USER_TYPE_LABEL'  => ucfirst($this->getUserTypeLabel($user)),
            'USER_META_INFO'   => $this->renderUserMetaInfo($user),
            'SAE_NAVIGATION'   => $this->renderSAENavigation($user),
            'SAE_CONTENT'      => $this->renderSAEContent($user, $saes)
        ];
    }

    /**
     * Returns a user-friendly label based on the user's role.
     *
     * @param User $user The user instance.
     *
     * @return string The role label.
     */
    private function getUserTypeLabel(User $user): string
    {
        if ($user->isStudent()) {
            return 'étudiant';
        }
        if ($user->isProfessor()) {
            return 'professeur';
        }
        if ($user->isClient()) {
            return 'client';
        }
        return 'utilisateur';
    }

    /**
     * Renders additional user information depending on the user type.
     *
     * @param User $user The user instance.
     *
     * @return string The HTML containing user metadata.
     */
    private function renderUserMetaInfo(User $user): string
    {
        $html = '';

        if ($user->isStudent() && $user instanceof Student) {
            /* @var Student $student */
            $student = $user;

            $html .= '<span>Année : ' . $student->getYear() . '</span>';
            $html .= '<span>Groupe : ' . $student->getTd() . '-' . $student->getTp() . '</span>';
            if ($student->getMajor()) {
                $html .= '<span>Parcours : ' . $student->getMajor() . '</span>';
            }
        } elseif ($user->isProfessor()) {
            $html .= '<span>Département : Informatique</span>';
        } elseif ($user->isClient() && $user instanceof Client) {
            /* @var Client $client */
            $client = $user;
            $html .= '<span>Entreprise : ' . $client->getOrganisation() . ' </span>';
        }

        return $html;
    }

    /**
     * Renders the SAE navigation depending on the user's role.
     *
     * @param User $user The user instance.
     *
     * @return string The HTML for SAE navigation.
     */
    private function renderSAENavigation(User $user): string
    {
        $html = '<div class="nav-section">';
        $html .= '<h3>SAE</h3>';

        if ($user->isProfessor()) {
            $html .= '<a class="btn-create" href="/sae/create">+ Créer une nouvelle SAE</a>';
        } elseif ($user->isStudent()) {
        } elseif ($user->isClient()) {
        }

        $html .= '</div>';
        return $html;
    }

    /**
     * Renders the SAE content section with cards or an empty message.
     *
     * @param User              $user The user instance.
     * @param array<SAESubject> $saes List of SAE data arrays.
     *
     * @return string The rendered HTML content.
     */
    private function renderSAEContent(User $user, array $saes): string
    {
        if (empty($saes)) {
            return $this->renderEmptyState($user);
        }

        $html = '<div class="sae-grid">';
        foreach ($saes as $sae) {
            $html .= $this->renderSAECard($user, $sae);
        }
        $html .= '</div>';

        return $html;
    }

    /**
     * Renders a single SAE card with its details.
     *
     * @param User       $user The user instance.
     * @param SAESubject $sae  The SAE data array.
     *
     * @return string The rendered HTML SAE card.
     */
    private function renderSAECard(User $user, SAESubject $sae): string
    {
        $html  = '<article class="sae-card">';
        $html .= '<div class="sae-header">';
        $html .= '<div class="sae-icon" aria-hidden="true">' . (string) $sae->getSaeSubjectId() . '</div>';
        $html .= '</div>';
        $html .= '<div class="sae-body">';
        $html .= '<h3>' . $sae->getSubjectName() . '</h3>';

        if (!empty($sae->getResponsibleProfId())) {
            /** @var array<int, string> $professors */
            $professors = $this->data['sae'];
            $profName = $professors[$sae->getSaeSubjectId()] ?? 'Non Connu';
            $html .= '<p><strong>Enseignant :</strong> ' . $profName . '</p>';
        }

        $html .= '<div class="sae-actions">';
        $html .= '<a href="/sae/' . intval($sae->getSaeSubjectId()) . '" class="btn btn-primary">Voir détails</a>';
        $html .= '</div></div></article>';

        return $html;
    }

    /**
     * Displays a message when no SAE is available.
     *
     * @param User $user The user instance.
     *
     * @return string The rendered HTML empty state.
     */
    private function renderEmptyState(User $user): string
    {
        $message = 'Aucune SAE disponible pour le moment.';
        $action  = '';

        if ($user->isProfessor()) {
            $message = 'Vous n\'avez pas encore créé de SAE.';
            $action  = '<a href="/sae/create" class="btn btn-primary">Créer votre première SAE</a>';
        } elseif ($user->isStudent()) {
            $message = 'Vous n\'êtes inscrit à aucune SAE actuellement.';
        }

        return '<div class="empty-state"><h4>' . $message . '</h4><p>' . $action . '</p></div>';
    }

    /**
     * Returns the page title for the dashboard.
     *
     * @return string The title of the dashboard page.
     */
    #[Override]
    protected function getPageTitle(): string
    {
        return 'Dashboard - SAE Manager';
    }

    /**
     * Returns the name of the CSS file associated with this view.
     *
     * @return string The CSS filename.
     */
    #[Override]
    protected function getNameCss(): string
    {
        return 'dashboard.css';
    }

    /**
     * Returns additional HTML headers for the dashboard.
     *
     * @return string The meta and OG headers.
     */
    #[Override]
    protected function getAdditionalHeaders(): string
    {
        return '<meta name="description" content="Tableau de bord utilisateur de SAE Manager">
                <meta name="keywords" content="SAE Manager, Dashboard, SAE, utilisateur">
                <meta name="author" content="Benhafessa-Edelstein-Dargentolle-Griguer-Radjou">';
    }
}
