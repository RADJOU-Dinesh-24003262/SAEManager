<?php

namespace Controllers;

use Core\Controllers\ControllerInterface;
use Core\Includes\Exception\ExceptionEmailAlreadyExists;
use Core\Utils\Logger;
use Core\Utils\SessionService;
use Models\Entity\User\User;
use Core\Includes\Exception\ExceptionBD\ExceptionFetchDataBD;
use Core\Includes\Exception\ExceptionCsrf;
use Core\Includes\Exception\ExceptionSpam;

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
        $this->ensureAuthenticated();

        if (!$this->user->isProfessor()) {
            SessionService::setFlash('errors', ['Accès réservé aux professeurs.']);
            $this->redirect('/dashboard');
        }
    }

    /**
     * Redirects to the given URL.
     *
     * @param string $url The URL to redirect to.
     * @return void
     */
    protected function redirect(string $url): void
    {
        header("Location: $url");
        return;
    }

    /**
     * Checks if the CSRF token is valid. If not, it logs the attempt and throws ExceptionCsrf.
     *
     * @param string $logActionName The prefix for the log message (e.g., 'LOGIN', 'REGISTER').
     * @return void
     * @throws ExceptionCsrf If the CSRF token is invalid.
     */
    protected function checkCsrf(string $logActionName): void
    {
        if (!SessionService::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            Logger::log($logActionName . '_CSRF_FAIL', 'Attempted action with invalid token.', null, 'WARNING');
            throw new ExceptionCsrf();
        }
    }

    /**
     * Checks if the honeypot field is empty. If not, it logs the attempt and throws ExceptionSpam.
     *
     * @param string $logActionName The prefix for the log message (e.g., 'LOGIN', 'REGISTER').
     * @return void
     * @throws ExceptionSpam If the honeypot field is filled.
     */
    protected function checkHoneypot(string $logActionName): void
    {
        if (!empty($_POST['telephone'])) {
            Logger::log(
                $logActionName . '_SPAM_FAIL',
                'Automated action attempt blocked by honeypot.',
                null,
                'WARNING'
            );
            throw new ExceptionSpam("Échec de la validation. Veuillez réessayer.");
        }
    }

    /**
     * Checks if the CSRF token is valid for an AJAX request. If not, it throws ExceptionCsrf.
     *
     * @param string $logActionName The prefix for the log message.
     * @return void
     * @throws ExceptionCsrf If the CSRF token is invalid.
     */
    protected function checkCsrfAjax(string $logActionName): void
    {
        $csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!SessionService::verifyCsrfToken($csrfToken)) {
            $userOrNull = isset($this->user) ? $this->user->getUserId() : null;
            Logger::log($logActionName . '_CSRF_FAIL', 'Invalid CSRF token.', $userOrNull, 'WARNING');
            throw new ExceptionCsrf("Session invalide (CSRF).");
        }
    }
}
