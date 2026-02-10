<?php

namespace App\GUI\Controllers\User;

use Core\Controllers\ControllerInterface;
use App\Domain\User\Exception\EmailAlreadyExistsException;
use App\Application\Validation\Exception\EmptyFieldsException;
use App\Application\Validation\Exception\RegisterValidationsException;
use App\Application\Validation\User\RegisterStudentValidator;
use App\Application\Validation\User\RegisterProfessorValidator;
use App\Application\Validation\User\RegisterClientValidator;
use App\Infrastructure\Security\InputSanitizer;
use App\Infrastructure\Service\Logger;
use App\Infrastructure\Service\SessionService;
use Exception;
use App\Application\User\RegisterUserUseCase;
use App\Infrastructure\Persistence\Pdo\PdoUserRepository;
use Override;
use PDOException;
use App\GUI\Views\User\RegisterSuccessView;
use App\GUI\Views\User\RegisterView;

/**
 * This class controls the register process (post).
 *
 * @category Controller
 * @package Src
 * @subpackage Controllers/User
 * @author Alexandre Benhafessa <alexandre.benhafessa@etu.univ-amu.fr>
 * @author François Dargentolle <francois.dargentolle@etu.univ-amu.fr>
 * @author William Edelstein <william.edelstein@etu.univ-amu.fr>
 * @author Nathan Griguer <nathan.griguer@etu.univ-amu.fr>
 * @author Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license MIT License https://opensource.org/licenses/MIT
 * @link https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class RegisterPost implements ControllerInterface
{
    /**
     * Principal manager of the controller
     *
     * @return void
     *
     * @throws Exception For any other unexpected errors during the registration process.
     */
    #[Override]
    public function control(): void
    {
        // CSRF Check.
        if (!SessionService::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            Logger::log('CSRF_FAIL', 'Tentative inscription avec token invalide.', null, 'WARNING');
            SessionService::setFlash('errors', ['general' => 'Session invalide, veuillez réessayer.']);
            $view = new RegisterView(['csrf_token' => SessionService::generateCsrfToken()]);
            $view->render();
            exit();
        }

        try {
            // Sanitize input data
            $data = InputSanitizer::sanitize($_POST);
            
            // Choose validator based on user type
            $validator = match($data['user_type'] ?? '') {
                'student' => new RegisterStudentValidator(),
                'professor' => new RegisterProfessorValidator(),
                'client' => new RegisterClientValidator(),
                default => throw new Exception("Type d'utilisateur invalide")
            };
            
            // Validate the data
            $validator->validate($data);

            // Create the user.
            $useCase = new RegisterUserUseCase(new PdoUserRepository());
            $user = $useCase->execute($data);

            Logger::log('REGISTER_SUCCESS', "Nouvel utilisateur enregistré: " . $user->getEmail());

            $view = new RegisterSuccessView($user);
            $view->render();
            exit();
        } catch (EmailAlreadyExistsException $e) {
            SessionService::setFlash('errors', ['email' => $e->getMessage()]);
            Logger::log('REGISTER_FAIL', "Email déjà utilisé: " . $e->getEmail(), null, 'INFO');
        } catch (RegisterValidationsException | EmptyFieldsException $e) {
            $errors = [];
            foreach ($e->getErrors() as $error) {
                $errors[] = $error->getMessage();
            }
            SessionService::setFlash('errors', $errors);
            Logger::log('REGISTER_FAIL', "Échec validation inscription IP: {$_SERVER['REMOTE_ADDR']}", null, 'INFO');
        } catch (PDOException $e) {
            Logger::log('DB_ERROR', "Erreur BDD lors de l'inscription: " . $e->getMessage(), null, 'CRITICAL');
            SessionService::setFlash('errors', ['general' => 'Une erreur est survenue, réessayez plus tard']);
        } catch (Exception $e) {
            Logger::log('REGISTER_ERROR', "Erreur lors de l'inscription: " . $e->getMessage(), null, 'ERROR');
            SessionService::setFlash('errors', ['general' => 'Erreur lors de l\'inscription: ' . $e->getMessage()]);
        }
        $view = new RegisterView(['csrf_token' => SessionService::generateCsrfToken()]);
        $view->render();
    }
    /**
     * Determines whether this controller supports the given request.
     *
     * @param string $path   The requested URI path.
     * @param string $method The HTTP method used in the request.
     *
     * @return boolean True if the path is "/register" and the method is POST.
     */
    #[Override]
    public static function support(string $path, string $method): bool
    {
        return $path === "/register" && $method === "POST";
    }
}
