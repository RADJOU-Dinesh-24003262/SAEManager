<?php

namespace Controllers\pwd;

use Controllers\ControllerInterface;
use Utilis\TokenService;
use Utilis\SessionService;
use Views\pwd\ResetPasswordView;
use includes\exception\ExceptionInvalidToken;

/**
 * Class User
 * This class controls the reset password process (get).

 * @package src

 * @subpackage Controllers\pwd

 * @author  Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 */
class ResetPasswordController implements ControllerInterface
{
    /**
     * Principal manager of the controller
     *
     * @return void
     */
    public function control(): void
    {
        // Get the token from the URL.
        $token = $_GET['token'] ?? '';
        try {
            // Validate the token
            $tokenData = TokenService::validateToken($token);

            // Token is valid, render the reset password view.
            $view = new ResetPasswordView($token, $tokenData['user_email']);
            $view->render();
        } catch (ExceptionInvalidToken $e) {
            SessionService::setFlash('errors', ['Erreur lors de la validation du lien: ' . $e->getMessage()]);
            header('Location: /forgot-password');
            exit();
        } catch (\PDOException $e) {
            error_log("Erreur validation token: " . $e->getMessage());
            SessionService::setFlash('errors', ['Erreur lors de la validation du lien: veuillez réessayer plus tard.']);
            header('Location: /');
        }
    }

    /**
     * Check if this controller can handle the request
     *
     * @param string $path   The request URI path.
     * @param string $method The HTTP request method (e.g., GET, POST).
     *
     * @return bool True if the path is "/reset-password" and the method is GET, false otherwise.
     */
    public static function support(string $path, string $method): bool
    {
        return $path === "/reset-password" && $method === "GET";
    }
}
