<?php
namespace Controllers\SAE;

use Controllers\BaseController;
use Core\Controllers\ControllerInterface;
use Core\includes\exception\ExceptionValidation\ExceptionValidationEmptys;
use Core\includes\exception\ExceptionValidation\ExeptionValidationSAECreation;
use Core\includes\exception\SAE\ExceptionInvalidData;
use Core\Utilis\SessionService;
use Exception;
use Models\SAE\SAE;
use Models\User\Client;
use Models\User\User;
use Services\FileService;
use Validator\CreateSaeValidator;
use Validator\ModifySaeValidator;
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
        $validator = new ModifySaeValidator();

        try {
            $data = $validator->escape($data);

            $user = $this->user;
            $validator->validate($data);


            $oldFileName = SAE::getInstance()->getFileName($user, $saeId);



            $fileName = FileService::saveSaeDescription($data['description'], $data['nameSae']);

            $updateData = [
                'subject_name' => $data['nameSae'],
                'client_id' => !empty($data['client_id']) ? intval($data['client_id']) : null,
                'begin_date' => $data['begin_date'],
                'end_date' => $data['date_rendu'],
                'file_path' => $fileName
            ];


            if (!empty($oldFileName)) {
                try {
                    FileService::removeFile($oldFileName);
                } catch (\Exception $e) {
                    error_log("Erreur suppression fichier: " . $e->getMessage());
                }
            }

            SAE::getInstance()->updateSAE($user, $saeId, $updateData);
            header('Location: /sae/' . $saeId);
            exit();
        } catch (ExeptionValidationSAECreation $e) {
            SessionService::setFlash('errors', $e->getMessage());
        } catch (ExceptionValidationEmptys $e) {
            $errors = array_map(fn($error) => $error->getMessage(), $e->getErrors());
            SessionService::setFlash('errors', $errors);
            header('Location: /sae/' . $saeId . '/modify');
            exit;
        } catch (\Exception $e) {
            SessionService::setFlash('errors', ['Erreur : ' . $e->getMessage()]);
            header('Location: /sae/' . $saeId . '/modify');
            exit;
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