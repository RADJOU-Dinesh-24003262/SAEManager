<?php

namespace Controllers\Password;

use Controllers\BaseController;
use Core\includes\exception\ExceptionToken\ExceptionInvalidToken;
use Core\Utils\SessionService;
use HandleTwoAuthentificationUseCase;
use Models\Repository\User\PdoClientRepository;
use Models\Repository\User\PdoPasswordResetRepository;
use Models\Repository\User\PdoPendingRegistrationRepository;
use Models\Repository\User\PdoProfessorRepository;
use Models\Repository\User\PdoStudentRepository;
use Models\Repository\User\PdoUserRepository;
use Models\UseCase\User\ValidateTokenUseCase;
use Override;
use Views\Password\ResetPasswordView;
use Views\User\RegisterSuccessView;
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
class TwoFactorAuthentificationController extends BaseController
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
            $pendingRegistrationsRepository = new PdoPendingRegistrationRepository();
            $studentRepo = new PdoStudentRepository();
            $professorRepo = new PdoProfessorRepository();
            $clientRepo = new PdoClientRepository();
            $userRepo = new PdoUserRepository();

            $data = $pendingRegistrationsRepository->findValidByToken($token);

            // Faire récupération de données dans pending_registrations puis recreer $user
            // + fonctions de registerPost avant puis afficher registerSuccess avec $user
            $handleTwoAuthentificationUseCase = new HandleTwoAuthentificationUseCase(                
                $studentRepo,
                $professorRepo,
                $clientRepo,
                $userRepo,
                $pendingRegistrationsRepository);
                
            $user = $handleTwoAuthentificationUseCase->execute($token);



            // Token is valid, render the reset password view.
            $view = new RegisterSuccessView($user);
            $view->render();
        } catch (ExceptionInvalidToken $e) {
            SessionService::setFlash('errors', ['Erreur lors de la validation du lien: ' . $e->getMessage()]);
            header('Location: /register');
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
        return $path === "/mfa" && $method === "GET";
    }
}
