<?php

namespace Views;

use Core\Views\AbstractView;
use Models\Entity\SAE\SAESubject;
use Models\Entity\User\User;

/**
 * Class BaseSaeView
 *
 * Base view class for all SAE-related pages.
 * Provides common functionality like generating the SAE menu and handling common template keys.
 *
 * @category   Views
 * @package    Src
 * @subpackage Views
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
abstract class BaseSaeView extends AbstractView
{
    /**
     * @var SAESubject
     */
    protected SAESubject $subject;

    /**
     * @var User
     */
    protected User $user;

    /**
     * Constructor.
     *
     * @param SAESubject $subject The SAE subject associated with the view.
     * @param User       $user    The current user.
     */
    public function __construct(SAESubject $subject, User $user)
    {
        parent::__construct([]);
        $this->subject = $subject;
        $this->user = $user;
    }

    /**
     * Returns the common template keys for SAE pages.
     *
     * @return array<string, int|string|null> The common template keys.
     */
    protected function getCommonSaeTemplateKeys(): array
    {
        return [
            'SAE_NUM' => $this->subject->getSaeSubjectId(),
            'SAE_NAME' => $this->subject->getSubjectName(),
            'SAE_MENU' => $this->getMenuSae()
        ];
    }

    /**
     * Generates the menu items based on the user's role.
     *
     * @return string The HTML for the menu items.
     */
    protected function getMenuSae(): string
    {
        $user = $this->user;
        $saeId = $this->subject->getSaeSubjectId();
        $menu = '';

        if ($user->isStudent()) {
            $menu .= '<li><h2>Mon Espace</h2></li>';
            $menu .= '<li><a href="/sae/' . $saeId . '/to-do">📋 Tableau de bord</a></li>';
            $menu .= '<li><a href="/sae/' . $saeId . '#contacts">👥 Contacts</a></li>';
        } elseif ($user->isProfessor()) {
            $menu .= '<li><h2>Navigation</h2></li>';
            if ($user->getUserId() === $this->subject->getResponsibleProfId()) {
                $menu .= '<li><a href="/sae/' . $saeId . '/modify">✏️ Modifier la SAE</a></li>';
                $menu .= '<li><a href="/sae/' . $saeId . '/groups">👥 Gérer les groupes</a></li>';
            }
            $menu .= '<li><a href="/sae/' . $saeId . '/to-do">📋 Tableau de bord (Suivi)</a></li>';
            $menu .= '<li><a href="/sae/' . $saeId . '#contacts">📧 Contacts</a></li>';
        } elseif ($user->isClient()) {
            $menu .= '<li><h2>Espace Client</h2></li>';
            $menu .= '<li><a href="/sae/' . $saeId . '#contacts">📧 Contacts</a></li>';
        }

        // Common link for everyone or fallback.
        $menu .= '<li><a href="/sae/' . $saeId . '">🏠 Menu Principal</a></li>';

        return $menu;
    }
}
