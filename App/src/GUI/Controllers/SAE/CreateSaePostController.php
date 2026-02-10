<?php

namespace App\GUI\Controllers\SAE;

use Core\Controllers\ControllerInterface;
 use App\Application\Validation\Exception\EmptyFieldsException;
use App\Application\Validation\Exception\SaeCreationValidationException;
use App\Domain\SAE\Exception\InvalidDataException;
use App\Infrastructure\Service\SessionService;
use App\Infrastructure\Security\InputSanitizer;
use Exception;
 use App\Application\SAE\CreateSaeUseCase;
 use App\Infrastructure\Persistence\Pdo\PdoSaeRepository;
 use App\Application\User\GetClientsUseCase;
 use App\Infrastructure\Persistence\Pdo\PdoUserRepository;
 use App\Domain\User\User;
 use Override;
 use App\Infrastructure\Service\FileService;
 use App\Application\Validation\SAE\CreateSaeValidator;
 use App\GUI\Views\SAE\CreateSaeView;
 
/**
 * Controller to handle the processing of the SAE creation form.
 *
 * @category   Controllers
 * @package    Src
 * @subpackage Controllers/SAE
 * @author     Dinesh Radjou <dinesh.radjou@univ-amu.fr>
 * @license    https://opensource.org/licenses/MIT MIT License
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager/blob/main/App/src/Controllers/SAE/CreateSaePostController.php
 */
class CreateSaePostController implements ControllerInterface
{
    /**
     * Controls the processing of the SAE creation form.
     *
     * @return void
     * @throws Exception If an unknown user is encountered.
     */
    #[Override]
    public function control(): void
    {
        // Redirect to /login if not logged in.
        if (!SessionService::has('user_id')) {
            SessionService::setFlash('errors', ['Authentification requise.']);
            header('Location: /login');
            exit();
        }

        // Retrieve the user object stored in the session.
        $user = unserialize(SessionService::get('USER'));

        $data['user'] = $user;

        if (!$user || !($user instanceof User)) {
            throw new Exception('Unknown user.');
        }

        $user = unserialize(SessionService::get('USER'));

        if (!$user->isProfessor()) {
            header('Location: /');
            exit();
        }

        $data = $_POST;
        $validator = new CreateSaeValidator();

        try {
            // Extract description before escape to preserve Markdown.
            $description = $data['description'];

            // Basic sanitization.
            $data = InputSanitizer::sanitize($data);

            // Restore description for length check and saving.
            $data['description'] = $description;

            // Validation.
            $validator->validate($data);

            // Save description as Markdown file.
            $filePath = FileService::saveSaeDescription($description, $data['nameSae']);

            $clientId = !empty($data['client_id']) ? intval($data['client_id']) : null;

            $saeData = [
                'responsible_prof_id' => $user->getUserId(),
                'client_id' => $clientId,
                'subject_name' => $data['nameSae'],
                'begin_date' => $data['begin_date'],
                'end_date' => $data['date_rendu'],
                'file_path' => $filePath
            ];

            $useCase = new CreateSaeUseCase(new PdoSaeRepository());
            $useCase->execute($user, $saeData);

            SessionService::setFlash('success', 'SAE créée avec succès !');
            header('Location: /dashboard');
            exit();
        } catch (SaeCreationValidationException $e) {
            SessionService::setFlash('errors', $e->getMessage());
        } catch (InvalidDataException $e) {
            SessionService::setFlash('errors', 'Données invalides fournies : ' . $e->getMessage());
        } catch (EmptyFieldsException $e) {
            $errors = array_map(fn ($error) => $error->getMessage(), $e->getErrors());
            SessionService::setFlash('errors', $errors);
        }

        $useCase = new GetClientsUseCase(new PdoUserRepository());
        $clients = $useCase->execute();

        $view = new CreateSaeView(['clients' => $clients]);
        $view->render();
    }

    /**
     * Determines if this controller supports the given path and method.
     *
     * @param string $path   The requested path.
     * @param string $method The HTTP method.
     *
     * @return boolean
     */
    #[Override]
    public static function support(string $path, string $method): bool
    {
        return $path === "/sae/create" && $method === "POST";
    }
}
