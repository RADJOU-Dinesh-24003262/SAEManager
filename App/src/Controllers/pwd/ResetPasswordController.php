<?php

namespace Controllers\pwd;

use Core\ControllerInterface;
use Core\Utilis\TokenService;
use Core\Utilis\SessionService;
use Views\pwd\ResetPasswordView;
use Core\includes\exception\ExceptionToken\ExceptionInvalidToken;

/**
 * This class controls the reset password process (get).
 *
 * @category   Controllers
 * @package    Src
 * @subpackage Controllers/pwd
 * @author     Alexandre Benhafessa <alexandre.benhafessa@etu.univ-amu.fr>
 * @author     François Dargentolle <francois.dargentolle@etu.univ-amu.fr>
 * @author     William Edelstein <william.edelstein@etu.univ-amu.fr>
 * @author     Nathan Griguer <nathan.griguer@etu.univ-amu.fr>
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class ResetPasswordController implements ControllerInterface
{
    /**
     * Principal manager of the controller.
     *
     * @return void
     */
    public function control(): void
    {
        // Get the token from the URL.
        $token = $_GET['token'] ?? '';
        try {
            // Validate the token.
            $tokenData = TokenService::validateToken($token);

            // Token is valid, render the reset password view.
            $view = new ResetPasswordView($token, $tokenData['email']);
            $view->render();
        } catch (ExceptionInvalidToken $e) {
            SessionService::setFlash('errors', ['Erreur lors de la validation du lien: ' . $e->getMessage()]);
            header('Location: /forgot-password');
            exit();
        } catch (\PDOException $e) {
            error_log("Erreur validation token: " . $e->getMessage());
            SessionService::setFlash(
                'errors',
                ['Erreur lors de la validation du lien: 
            veuillez réessayer plus tard.']
            );
            header('Location: /');
        }
    }

    /**
     * Check if this controller can handle the request
     *
     * @param string $path   The request URI path.
     * @param string $method The HTTP request method (e.g., GET, POST).
     *
     * @return boolean True if the path is "/reset-password" and the method is GET, false otherwise.
     */
    public static function support(string $path, string $method): bool
    {
        return $path === "/reset-password" && $method === "GET";
    }
}
