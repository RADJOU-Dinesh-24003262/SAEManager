<?php

namespace Controllers\SAE;

use Controllers\BaseController;
use Core\Controllers\ControllerInterface;
use Core\includes\exception\ExceptionValidation\ExceptionValidationEmptys;
use Core\includes\exception\ExceptionValidation\ExeptionValidationSAECreation;
use Core\includes\exception\SAE\ExceptionInvalidData;
use Core\Utilis\SessionService;
use Models\SAE\SAE;
use Models\User\Client;
use Models\User\User;
use Services\FileService;
use Validator\CreateSaeValidator;
use Views\SAE\CreateSaeView;

class ModifySaePostController extends BaseController
{

    /**
     * @return void
     */
    public function control(): void
    {
        $this->ensureProfessor();

        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '';
        if (preg_match('/^\/sae\/(\d+)\/modify$/', $path, $matches)) {
            $saeId = intval($matches[1]);
        } else {
            header('Location: /dashboard');
            exit;
        }

        if (!$this->user->canManageSAE($saeId)) {
            SessionService::setFlash('errors', ["Vous n'avez pas la permission de modifier cette SAE."]);
            header('Location: /sae/' . $saeId);
            exit;
        }

        $data = $_POST;
        $validator = new CreateSaeValidator();

        try {
            $data = $validator->escape($data);

            $user = $this->user;
            $validator->validate($data);

            $fileData = FileService::saveSaeDescription($data['description'], $data['nameSae']);

            $updateData = [
                'subject_name' => $data['nameSae'],
                'client_id' => !empty($data['client_id']) ? intval($data['client_id']) : null,
                'begin_date' => $data['begin_date'],
                'end_date' => $data['date_rendu'],
                'file_path' => $fileData['file_path']
            ];

            SAE::getInstance()->updateSAE($user, $saeId, $updateData);

            SessionService::setFlash('success', 'SAE modifiée avec succès !');
            header('Location: /sae/' . $saeId);
            exit();
        } catch (ExeptionValidationSAECreation $e) {
            SessionService::setFlash('errors', $e->getMessage());
        } catch (ExceptionInvalidData $e) {
            SessionService::setFlash('errors', 'Données invalides fournies : ' . $e->getMessage());
        } catch (ExceptionValidationEmptys $e) {
            $errors = array_map(fn ($error) => $error->getMessage(), $e->getErrors());
            SessionService::setFlash('errors', $errors);
        }
    }

    /**
     * @param string $path
     * @param string $method
     * @return bool
     */
    public static function support(string $path, string $method): bool
    {
        return preg_match('/^\/sae\/\d+\/modify$/', $path) && $method === "POST";
    }
}