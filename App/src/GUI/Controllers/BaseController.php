<?php

namespace App\GUI\Controllers;

use Core\Controllers\ControllerInterface;
use App\Domain\User\User;
use App\GUI\Middleware\AuthMiddleware;

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
        $this->user = AuthMiddleware::authenticate();
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

        AuthMiddleware::ensureProfessor($this->user);
    }
}