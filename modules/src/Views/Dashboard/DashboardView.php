<?php

namespace Views\Dashboard;

use Models\User\User;
use Utilis\SessionService;
use Views\AbstractView;

/**
 * Class DashboardView
 *
 * Responsible for rendering the dashboard page for users,
 * including SAE navigation and user-specific information.
 *
 * @package Views\Dashboard
 */
class DashboardView extends AbstractView
{
    /**
     * Path to the HTML template file for the dashboard.
     */
    private const TEMPLATE_HTML = __DIR__ . '/dashboard.html';

    /**
     * DashboardView constructor.
     *
     * Initializes the view with session flash messages and user data.
     *
     * @param array $data - Initial data for the view (expects 'user' key)
     */
    public function __construct($data)
    {
        $user = $data['user'];
        $data = [
            'errors' => SessionService::getFlash('errors', []),
            'user' => $user
        ];
        parent::__construct($data);
    }

    /**
     * Returns the path to the dashboard template.
     *
     * @return string
     */
    protected function templatePath(): string
    {
        return self::TEMPLATE_HTML;
    }

    /**
     * Returns the list of template keys and their corresponding rendered values.
     *
     * @return array
     */
    protected function templateKeys(): array
    {
        $errors = $this->data['errors'] ?? [];
        $user = $this->data['user'];
        $saes = $this->data['saes'] ?? [];

        return [
            'ERROR_MESSAGES' => $this->renderErrorMessages($errors),
            'SUCCESS_MESSAGE' => $this->renderSuccessMessage(),
            'USER_NAME' => $user->getFullName(),
            'USER_EMAIL' => $user->getEmail(),
            'USER_TYPE_CLASS' => $this-> getUserTypeLabel($user),
            'USER_TYPE_LABEL' => $this->getUserTypeLabel($user),
            'USER_META_INFO' => $this->renderUserMetaInfo($user),
            'SAE_NAVIGATION' => $this->renderSAENavigation($user),
            'SAE_CONTENT' => $this->renderSAEContent($user, $saes)
        ];
    }

    /**
     * Renders error messages into HTML format.
     *
     * @param  array $errors
     * @return string
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
     * Renders a success message if available.
     *
     * @return string
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
     * @param  User $user
     * @return string
     */
    private function getUserTypeLabel(User $user): string
    {
        if ($user->isStudent()) {
            return 'etudiant';
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
     * Renders additional user information based on user type.
     *
     * @param  User $user
     * @return string
     */
    private function renderUserMetaInfo(User $user): string
    {
        $html = '';

        if ($user->isStudent()) {
            $html .= '<span>Année : ' . $user->getYear() . '</span>';
            $html .= '<span>Groupe : ' . $user->getTd() . '-' . $user->getTp() . '</span>';
            if ($user->getParcours()) {
                $html .= '<span>Parcours : ' . $user->getParcours() . '</span>';
            }
        } elseif ($user->isProfessor()) {
            $html .= '<span>AMU ID : ' . 'TODO' . '</span>';
            $html .= '<span>Département : Informatique</span>';
        } elseif ($user->isClient()) {
            $html .= '<span>Entreprise : ' . 'TODO' . '</span>';
        }

        return $html;
    }

    /**
     * Renders the SAE navigation links and buttons depending on the user's role.
     *
     * @param  User $user
     * @return string
     */
    private function renderSAENavigation(User $user): string
    {
        $html = '<div class="nav-section">';
        $html .= '<h3>SAE</h3>';

        if ($user->isProfessor()) {
            $html .= '<button class="btn-create" href="/sae/create">+ Créer une nouvelle SAE</button>';
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
     * Renders the SAE content section with SAE cards or an empty state.
     *
     * @param  User  $user
     * @param  array $saes
     * @return string
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
     * @param  User  $user
     * @param  array $sae
     * @return string
     */
    private function renderSAECard(User $user, array $sae): string
    {
        $html = '<article class="sae-card">';
        $html .= '<div class="sae-header">';
        $html .= '<div class="sae-icon" aria-hidden="true">' . $sae['code'] . '</div>';
        $html .= '</div>';
        $html .= '<div class="sae-body">';
        $html .= '<h3>' . $sae['title'] . '</h3>';
        $html .= '<p><strong>Compétences :</strong> ' . $sae['competences'] . '</p>';

        if (isset($sae['teacher'])) {
            $html .= '<p><strong>Enseignant :</strong> ' . $sae['teacher'] . '</p>';
        }

        $html .= '<div class="sae-actions">';
        $html .= '<a href="/sae/view/' . $sae['id'] . '" class="btn btn-primary">Voir détails</a>';

        $html .= '</div>';
        $html .= '</article>';

        return $html;
    }

    /**
     * Renders a message when there are no SAEs to display.
     *
     * @param  User $user
     * @return string
     */
    private function renderEmptyState(User $user): string
    {
        $message = 'Aucune SAE disponible pour le moment.';
        $action = '';

        if ($user->isProfessor()) {
            $message = 'Vous n\'avez pas encore créé de SAE.';
            $action = '<a href="/sae/create" class="btn btn-primary">Créer votre première SAE</a>';
        } elseif ($user->isStudent()) {
            $message = 'Vous n\'êtes inscrit à aucune SAE actuellement.';
        }

        $html = '<div class="empty-state">';
        $html .= '<h3>' . $message . '</h3>';
        $html .= '<p>' . $action . '</p>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Returns the page title for the dashboard.
     *
     * @return string
     */
    protected function getPageTitle(): string
    {
        return 'Dashboard - SAEManager';
    }

    /**
     * Returns the name of the CSS file for the dashboard page.
     *
     * @return string
     */
    protected function getNameCss(): string
    {
        return 'dashboard.css';
    }

    /**
     * Returns any additional HTML headers needed.
     *
     * @return string
     */
    protected function getAdditionalHeaders(): string
    {
        return '';
    }
}
