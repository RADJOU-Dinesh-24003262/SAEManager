<?php

namespace Controllers\SAE;

use Controllers\BaseController;
use Core\includes\exception\ExceptionValidation\ExceptionValidationEmptys;
use Core\includes\exception\ExceptionValidation\ExeptionValidationSAECreation;
use Core\Utilis\SessionService;
use Exception;
use Models\SAE\SAE;
use Override;
use Services\FileService;
use Validator\FormSaeValidator;

/**
 * This class controls the modification of an SAE via POST request.
 *
 * @category Controller
 *
 * @package Src
 *
 * @subpackage Controllers/SAE
 *
 * @author Alexandre Benhafessa <alexandre.benhafessa@etu.univ-amu.fr>
 * @author François Dargentolle <francois.dargentolle@etu.univ-amu.fr>
 * @author William Edelstein <william.edelstein@etu.univ-amu.fr>
 * @author Nathan Griguer <nathan.griguer@etu.univ-amu.fr>
 * @author Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 *
 * @license MIT License https://opensource.org/licenses/MIT
 *
 * @link https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class ModifySaePostController extends BaseController
{
    /**
     * Principal manager of the controller
     *
     * @return void
     * @throws ExceptionValidationEmptys If required fields are empty.
     * @throws ExeptionValidationSAECreation If validation fails during SAE modification.
     * @throws Exception If a general error occurs during the modification process.
     */
    #[Override]
    public function control(): void
    {
        $this->ensureProfessor();

        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        if (!is_string($path)) {
            $path = '';
        }

        if (preg_match('/^\/sae\/(\d+)\/modify$/', $path, $matches)) {
            $saeId = intval($matches[1]);
        } else {
            header('Location: /dashboard');
            exit;
        }

        // CSRF Protection.
        if (!SessionService::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            SessionService::setFlash('errors', ['general' => 'Session invalide, veuillez réessayer.']);
            header('Location: /sae/' . $saeId . '/modify');
            exit();
        }

        if (!$this->user->canManageSAE($saeId)) {
            SessionService::setFlash('errors', ["Vous n'avez pas la permission de modifier cette SAE."]);
            header('Location: /sae/' . $saeId);
            exit;
        }

        $data = $_POST;
        $validator = new FormSaeValidator();

        try {
            $data = $validator->escape($data);

            $user = $this->user;

            // Extract description before escape to preserve Markdown.
            $description = $data['description'];

            // Basic sanitization.
            $data = $validator->escape($data);

            // Restore description for length check and saving.
            $data['description'] = $description;

            $fileName = SAE::getInstance()->getFileName($user, $saeId);


            $updateData = [
                'subject_name' => $data['nameSae'],
                'client_id' => !empty($data['client_id']) ? intval($data['client_id']) : null,
                'begin_date' => $data['begin_date'],
                'end_date' => $data['date_rendu'],
                'file_path' => $fileName, // Keep old file by default.
            ];


            if (!empty($fileName)) {
                try {
                    FileService::updateSaeDescription($fileName, $description);
                } catch (\Exception $e) {
                    error_log("Erreur mise à jour fichier: " . $e->getMessage());
                    SessionService::setFlash('errors', [
                        'description' => 'Erreur lors de la mise à jour du fichier de description.'
                    ]);
                    header('Location: /sae/' . $saeId . '/modify');
                    exit();
                }
            }

            SAE::getInstance()->updateSAE($user, $saeId, $updateData);
            SessionService::setFlash('success', 'SAE modifiée avec succès');
            header('Location: /sae/' . $saeId);
            exit();
        } catch (ExeptionValidationSAECreation $e) {
            SessionService::setFlash('errors', $e->getMessage());
            header('Location: /sae/' . $saeId . '/modify');
            exit();
        } catch (ExceptionValidationEmptys $e) {
            $errors = array_map(fn($error) => $error->getMessage(), $e->getErrors());
            SessionService::setFlash('errors', $errors);
            header('Location: /sae/' . $saeId . '/modify');
            exit();
        } catch (\Exception $e) {
            SessionService::setFlash('errors', ['Erreur : ' . $e->getMessage()]);
            header('Location: /sae/' . $saeId . '/modify');
            exit();
        }
    }

    /**
     * Check if this controller can handle the request
     *
     * @param string $path   The request path.
     * @param string $method The HTTP request method.
     *
     * @return boolean True if the controller supports the request, otherwise false
     */
    #[Override]
    public static function support(string $path, string $method): bool
    {
        return preg_match('/^\/sae\/\d+\/modify$/', $path) && $method === "POST";
    }
}
