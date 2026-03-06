<?php

namespace Controllers;

use Core\Controllers\ControllerInterface;
use Core\Includes\Exception\ExceptionEmailAlreadyExists;
use Core\Utils\Logger;
use Core\Utils\SessionService;
use Models\Entity\User\User;
use Core\Includes\Exception\ExceptionBD\ExceptionFetchDataBD;

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
        exit;
    }

    /**
     * Checks if the CSRF token is valid. If not, it logs the attempt, sets a flash error, and redirects.
     *
     * @param string $logActionName The prefix for the log message (e.g., 'LOGIN', 'REGISTER').
     * @param string $redirectUrl   The URL to redirect to upon failure.
     * @return void
     */
    protected function checkCsrf(string $logActionName, string $redirectUrl): void
    {
        if (!SessionService::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            Logger::log($logActionName . '_CSRF_FAIL', 'Tentative action avec token invalide.', null, 'WARNING');
            SessionService::setFlash('errors', ['general' => 'Session invalide, veuillez réessayer.']);
            header("Location: $redirectUrl");
            exit();
        }
    }

    /**
     * Checks if the CSRF token is valid for an AJAX request. If not, it returns a 403 JSON response.
     *
     * @param string $logActionName The prefix for the log message.
     * @return void
     */
    protected function checkCsrfAjax(string $logActionName): void
    {
        $csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!SessionService::verifyCsrfToken($csrfToken)) {
            $userOrNull = isset($this->user) ? $this->user->getUserId() : null;
            Logger::log($logActionName . '_CSRF_FAIL', 'Invalid CSRF token.', $userOrNull, 'WARNING');
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Session invalide (CSRF).']);
            exit();
        }
    }
}
