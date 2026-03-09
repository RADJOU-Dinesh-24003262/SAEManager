<?php

namespace Controllers\ResetPassword;

use Controllers\BaseController;
use Core\Includes\Exception\ExceptionToken\ExceptionInvalidToken;
use Core\Utils\SessionService;
use Services\TokenService;
use Views\Password\ResetPasswordView;
use Override;

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
class ResetPasswordController extends BaseController
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
    #[Override]
    public static function support(string $path, string $method): bool
    {
        return $path === "/reset-password" && $method === "GET";
    }
}
