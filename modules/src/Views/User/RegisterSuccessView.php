<?php
namespace Views\User;

use Views\AbstractView;
use Models\User\User;

class RegisterSuccessView extends AbstractView
{
    private const TEMPLATE_HTML = __DIR__ . '/register-success.html';
    private const CSS_REGISTER_SUCCESS = 'register-success.css';
    private User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
        parent::__construct();
    }

    protected function templatePath(): string
    {
        return self::TEMPLATE_HTML;
    }

    protected function templateKeys(): array
    {
        return [
            'USER_FULL_NAME' => $this->user->getFullName(),
            'USER_EMAIL' => $this->user->getEmail(),
            'USER_TYPE_LABEL' => $this->getUserTypeLabel(),
            'ACADEMIC_INFO' => $this->getAcademicInfo()
        ];
    }

    private function getUserTypeLabel(): string
    {
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

    protected function getPageTitle(): string
    {
        return 'Inscription réussie - SAEManager';
    }

    protected function getNameCss(): string
    {
        return self::CSS_REGISTER_SUCCESS;
    }
}