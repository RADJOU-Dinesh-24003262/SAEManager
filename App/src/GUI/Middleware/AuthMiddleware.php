<?php

namespace App\GUI\Middleware;

use App\Infrastructure\Service\SessionService;
use App\Domain\User\User;

class AuthMiddleware
{
    /**
     * Authenticate user from session.
     * Redirects if not authenticated.
     */
    public static function authenticate(): User
    {
        if (!SessionService::has('user_id')) {
            SessionService::setFlash('errors', ['Authentification requise.']);
            header('Location: /login');
            exit();
        }

        $user = unserialize(SessionService::get('USER'));

        if (!($user instanceof User)) {
            SessionService::remove('USER');
            SessionService::remove('user_id');
            SessionService::setFlash('errors', ['Session invalide, veuillez vous reconnecter.']);
            header('Location: /login');
            exit();
        }

        return $user;
    }

    /**
     * Ensures the authenticated user is a professor.
     * Redirects to dashboard if not.
     */
    public static function ensureProfessor(User $user): void
    {
        if (!$user->isProfessor()) {
            SessionService::setFlash('errors', ['Accès réservé aux professeurs.']);
            header('Location: /dashboard');
            exit();
        }
    }
}