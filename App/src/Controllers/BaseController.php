<?php

namespace Controllers;

use Core\Controllers\ControllerInterface;
use Core\Utilis\SessionService;
use Models\User\User;

/**
 * Abstract BaseController to handle common controller logic like authentication.
 *
 * @category   Controllers
 * @package    Src
 * @subpackage Controllers
 * @author     Dinesh RADJOU <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
abstract class BaseController implements ControllerInterface
{
    /**
     * @var User The authenticated user.
     */
    protected User $user;

    /**
     * Ensures the user is authenticated.
     * Redirects to login page if not.
     * Populates $this->user.
     *
     * @return void
     */
    protected function ensureAuthenticated(): void
    {
        if (!SessionService::has('user_id')) {
            SessionService::setFlash('errors', ['Authentification requise.']);
            header('Location: /login');
            exit();
        }

        // Load and validate user from session.
        $user = unserialize(SessionService::get('USER'));

        if (!$user || !($user instanceof User)) {
            SessionService::remove('USER');
            SessionService::remove('user_id');
            SessionService::setFlash('errors', ['Session invalide, veuillez vous reconnecter.']);
            header('Location: /login');
            exit();
        }

        $this->user = $user;
    }

    /**
     * Ensures the authenticated user is a professor.
     * Redirects to dashboard if not.
     *
     * @return void
     */
    protected function ensureProfessor(): void
    {
        // Ensure user is loaded.
        if ($this->user == null) {
            $this->ensureAuthenticated();
        }

        if (!$this->user->isProfessor()) {
            SessionService::setFlash('errors', ['Accès réservé aux professeurs.']);
            header('Location: /dashboard');
            exit();
        }
    }
}
