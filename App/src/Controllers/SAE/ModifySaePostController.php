<?php

namespace Controllers\SAE;

use Controllers\BaseController;
use Core\includes\exception\ExceptionValidation\ExceptionValidationEmptys;
use Core\includes\exception\ExceptionValidation\ExeptionValidationSAECreation;
use Core\Utilis\SessionService;
use Exception;
use Models\Repository\SAE\PdoSAESubjectRepository;
use Models\SAE\SAE;
use Models\UseCase\SAE\ModifySAEUseCase;
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
     * @param integer $saeId The SAE ID.
     *
     * @return void
     * @throws ExceptionValidationEmptys If required fields are empty.
     * @throws ExeptionValidationSAECreation If validation fails during SAE modification.
     * @throws Exception If a general error occurs during the modification process.
     */
    public function control(int $saeId = 0): void
    {
        $this->ensureProfessor();

        // CSRF Protection.
        if (!SessionService::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            SessionService::setFlash('errors', ['general' => 'Session invalide, veuillez réessayer.']);
            header('Location: /sae/' . $saeId . '/modify');
            exit();
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

            $updateData = [
                'subject_name' => $data['nameSae'],
                'client_id' => !empty($data['client_id']) ? intval($data['client_id']) : null,
                'begin_date' => $data['begin_date'],
                'end_date' => $data['date_rendu'],
            ];

            $repository = new PdoSAESubjectRepository();
            $useCase = new ModifySAEUseCase($repository);

            $useCase->execute($saeId, $updateData, $description, $user);

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
