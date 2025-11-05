<?php

namespace Views\Dashboard;

use Models\User\User;
use Core\Utilis\SessionService;
use Core\AbstractView;
use Models\SAE\SAE;
use Models\User\Student;

/**
 * Class DashboardView
 *
 * Represents the view for the user dashboard page of SAEManager.
 * This page displays personalized information about the connected user
 * (name, email, role, SAE list, etc.) and provides navigation elements
 * specific to their role (student, professor, or client).
 *
 * @category   View
 * @package    Src
 * @subpackage Views\Dashboard
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
     * @param array<string, mixed> $data Data passed to the view, including 'user' and 'saes'.
     */
    public function __construct(array $data)
    {
        $data = [
            'errors'  => SessionService::getFlash('errors', []),
            'success' => SessionService::getFlash('success', ''),
            'user'    => $data['user'],
            'saes'    => $data['saes']
        ];

        parent::__construct($data);
    }

    /**
     * Returns the path to the dashboard HTML template file.
     *
     * @return string The template path.
     */
    protected function templatePath(): string
    {
        return self::TEMPLATE_HTML;
    }

    /**
     * Returns the list of keys and rendered values used in the HTML template.
     *
     * @return array<string, string> The list of template keys and values.
     */
    protected function templateKeys(): array
    {
        $errors = $this->data['errors'] ?? [];
        $user   = $this->data['user'];
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
     * Renders error messages in HTML format.
     *
     * @param array<string> $errors List of error messages.
     *
     * @return string The rendered HTML or an empty string.
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

    /**
     * Renders a success message in HTML format if available.
     *
     * @return string The rendered HTML or an empty string.
     */
    private function renderSuccessMessage(): string
    {
        $success = $this->data['success'] ?? '';
        if (empty($success)) {
            return '';
        }

        return '<div class="alert alert-success">' . $success . '</div>';
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
            if ($student->getParcours()) {
                $html .= '<span>Parcours : ' . $student->getParcours() . '</span>';
            }
        } elseif ($user->isProfessor()) {
            $html .= '<span>Département : Informatique</span>';
        } elseif ($user->isClient()) {
            $html .= '<span>Entreprise : À définir</span>';
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
            $html .= '<a href="/sae">Toutes les SAE</a>';
            $html .= '<a href="/student">Gérer les étudiants</a>';
        } elseif ($user->isStudent()) {
            $html .= '<a href="/sae">Mes SAE</a>';
            $html .= '<a href="/group">Mon Groupe</a>';
        } elseif ($user->isClient()) {
            $html .= '<a href="/sae">Mes SAE</a>';
        }

        $html .= '</div>';
        return $html;
    }

    /**
     * Renders the SAE content section with cards or an empty message.
     *
     * @param User               $user The user instance.
     * @param array<string, SAE> $saes List of SAE data arrays.
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
     * @param User $user The user instance.
     * @param SAE  $sae  The SAE data array.
     *
     * @return string The rendered HTML SAE card.
     */
    private function renderSAECard(User $user, SAE $sae): string
    {
        $html  = '<article class="sae-card">';
        $html .= '<div class="sae-header">';
        $html .= '<div class="sae-icon" aria-hidden="true">' . $sae->getSaeSubjectId() . '</div>';
        $html .= '</div>';
        $html .= '<div class="sae-body">';
        $html .= '<h3>' . $sae->getSubjectName() . '</h3>';
        $html .= '<p><strong>Compétences :</strong> ' . '$sae->getCompetences()' . '</p>';

        if (!empty($sae->getResponsibleProfId())) {
            $html .= '<p><strong>Enseignant :</strong> ' . $sae->getResponsibleProfId() . '</p>';
        }

        $html .= '<div class="sae-actions">';
        $html .= '<a href="/sae/view/' . intval($sae->getSaeSubjectId()) . '" class="btn btn-primary">Voir détails</a>';
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

        return '<div class="empty-state"><h3>' . $message . '</h3><p>' . $action . '</p></div>';
    }

    /**
     * Returns the page title for the dashboard.
     *
     * @return string The title of the dashboard page.
     */
    protected function getPageTitle(): string
    {
        return 'Dashboard - SAEManager';
    }

    /**
     * Returns the name of the CSS file associated with this view.
     *
     * @return string The CSS filename.
     */
    protected function getNameCss(): string
    {
        return 'dashboard.css';
    }

    /**
     * Returns additional HTML headers for the dashboard.
     *
     * @return string The meta and OG headers.
     */
    protected function getAdditionalHeaders(): string
    {
        return '<meta name="description" content="Tableau de bord utilisateur de SAEManager">
                <meta name="keywords" content="SAEManager, Dashboard, SAE, utilisateur">
                <meta name="author" content="Benhafessa-Edelstein-Dargentolle-Griguer-Radjou">';
    }
}
