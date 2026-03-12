<?php

namespace Controllers\Sae;

use Controllers\BaseController;
use Core\Controllers\ControllerInterface;
use Core\Includes\Exception\ExceptionValidation\ExceptionValidationEmptys;
use Core\Includes\Exception\ExceptionValidation\ExeptionValidationSAECreation;
use Core\Includes\Exception\SAE\ExceptionInvalidData;
use Core\Utils\SessionService;
use Exception;
use Models\Repository\SAE\PdoSAESubjectRepository;
use Models\Repository\User\PdoClientRepository;
use Models\UseCase\SAE\CreateSAEUseCase;
use Models\Entity\User\Client;
use Models\Entity\User\User;
use Override;
use Validator\CreateSaeValidator;
use Services\FileService;
use Validator\Sae\FormSaeValidator;
use Views\SAE\CreateSaeView;

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
class SaeCreatePostController extends BaseController
{
    /**
     * Controls the processing of the SAE creation form.
     *
     * @return void
     * @throws Exception If an unknown user is encountered.
     */
    public function control(): void
    {
        $this->ensureProfessor();

        $data = $_POST;
        $validator = new FormSaeValidator();

        try {
            // Basic sanitization.
            $data = $validator->escape($data);

            // Validation.
            $validator->validate($data);

            $clientId = !empty($data['client_id']) ? intval($data['client_id']) : null;

            $saeData = [
                'responsible_prof_id' => $this->user->getUserId(),
                'client_id' => $clientId,
                'subject_name' => $data['subject_name'],
                'begin_date' => $data['begin_date'],
                'end_date' => $data['end_date'],
                'description' => $data['description']
            ];

            $subjectInterface = new PdoSAESubjectRepository();
            $createSAEUseCase = new CreateSAEUseCase($subjectInterface);
            $createSAEUseCase->execute($this->user, $saeData);

            SessionService::setFlash('success', 'SAE créée avec succès !');
            header('Location: /dashboard');
            exit();
        } catch (ExeptionValidationSAECreation $e) {
            SessionService::setFlash('errors', $e->getMessage());
        } catch (ExceptionInvalidData $e) {
            SessionService::setFlash('errors', 'Données invalides fournies : ' . $e->getMessage());
        } catch (ExceptionValidationEmptys $e) {
            $errors = array_map(fn ($error) => $error->getMessage(), $e->getErrors());
            SessionService::setFlash('errors', $errors);
        }

        $clientInterface = new PdoClientRepository();
        $clients = $clientInterface->findAll();

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
