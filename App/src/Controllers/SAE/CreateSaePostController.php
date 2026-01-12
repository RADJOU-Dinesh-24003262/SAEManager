<?php

namespace Controllers\SAE;

use Controllers\BaseController;
use Core\includes\exception\ExceptionValidation\ExceptionValidationEmptys;
use Core\includes\exception\ExceptionValidation\ExeptionValidationSAECreation;
use Core\includes\exception\SAE\ExceptionInvalidData;
use Core\Utilis\SessionService;
use Exception;
use Models\SAE\SAE;
use Models\User\Client;
use Models\User\User;
use Override;
use Services\FileService;
use Validator\CreateSaeValidator;
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
class CreateSaePostController extends BaseController
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
        $this->ensureProfessor();
        $user = $this->user;

        $data = $_POST;
        $data['user'] = $user;
        $validator = new CreateSaeValidator();

        try {
            // Extract description before escape to preserve Markdown.
            $description = $data['description'];

            // Basic sanitization.
            $data = $validator->escape($data);

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

            SAE::getInstance()->createSAE($user, $saeData);

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
        $clients = Client::getAllClients();
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
