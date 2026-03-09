<?php

namespace Controllers\Sae;

use Controllers\BaseController;
use Core\Utils\SessionService;
use Models\Repository\SAE\PdoSAEGroupRepository;
use Models\Repository\SAE\PdoSAESubjectRepository;
use Models\UseCase\SAE\CreateSAEGroupUseCase;
use Exception;

/**
 * Controller to handle POST actions for group creation.
 *
 * @category   Controllers
 * @package    Src
 * @subpackage Controllers/Sae
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class SaeGroupsCreatePostController extends BaseController
{
    /**
     * Controls the processing of group creation.
     *
     * @param integer $saeId The SAE ID.
     *
     * @return void
     */
    public function control(int $saeId = 0): void
    {
        $this->ensureProfessor();

        try {
            $professorId = filter_input(INPUT_POST, 'professor_id', FILTER_VALIDATE_INT);
            if ($professorId == false) {
                $professorId = null;
            }

            $useCase = new CreateSAEGroupUseCase(
                new PdoSAEGroupRepository(),
                new PdoSAESubjectRepository()
            );
            $useCase->execute($this->user, $saeId, $professorId);

            SessionService::setFlash('success', 'Groupe créé avec succès.');
        } catch (Exception $e) {
            SessionService::setFlash('errors', $e->getMessage());
        }

        header('Location: /sae/' . $saeId . '/groups');
        exit;
    }

    /**
     * Check if this controller can handle the request.
     *
     * @param string $path   The requested URI path.
     * @param string $method The HTTP method used in the request.
     *
     * @return boolean True if supported.
     */
    #[\Override]
    public static function support(string $path, string $method): bool
    {
        return $method === 'POST' && preg_match('/^\/sae\/\d+\/groups\/create$/', $path);
    }
}
