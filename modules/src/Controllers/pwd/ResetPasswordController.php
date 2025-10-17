<?php
namespace Controllers\pwd;

use Controllers\ControllerInterface;
use Utilis\TokenService;
use Utilis\SessionService;
use Views\pwd\ResetPasswordView;
use includes\exception\ExceptionInvalidToken;

/**
 * Class User
 
 * @package     src

 * @subpackage  Controllers\pwd

 * @author      Benhafessa Alexandre, Dargentolle Francois, Edelstein William, Griguer Nathan, Radjou Dinesh

 * This class controls the reset password process (get).
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
        // Get the token from the URL
        $token = $_GET['token'] ?? '';
        try {         
            // Validate the token
            $tokenData = TokenService::validateToken($token);

            // Token is valid, render the reset password view
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
     * @return boolean Is the method get?
     */
    public static function support(string $chemin, string $method): bool
    {
        return $chemin === "/reset-password" && $method === "GET";
    }
}