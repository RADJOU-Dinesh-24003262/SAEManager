<?php

namespace Controllers\Sae;

use Controllers\BaseController;
use Core\Utils\SessionService;
use Models\Repository\SAE\PdoParticipatedInRepository;
use Models\Repository\SAE\PdoSAEGroupRepository;
use Models\Repository\SAE\PdoSAESubjectRepository;
use Models\UseCase\SAE\AssignStudentToGroupUseCase;
use Exception;

/**
 * Controller to handle POST actions for adding a student to a group.
 *
 * @category   Controllers
 * @package    Src
 * @subpackage Controllers/Sae
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class SaeGroupsAddStudentPostController extends BaseController
{
    /**
     * Controls the processing of adding a student.
     *
     * @param integer $saeId The SAE ID.
     *
     * @return void
     * @throws Exception If input variables are missing.
     */
    public function control(int $saeId = 0): void
    {
        $this->ensureProfessor();

        try {
            $groupId = filter_input(INPUT_POST, 'group_id', FILTER_VALIDATE_INT);
            $studentId = filter_input(INPUT_POST, 'student_id', FILTER_VALIDATE_INT);
            if (!$groupId || !$studentId) {
                throw new Exception("Données manquantes");
            }

            $useCase = new AssignStudentToGroupUseCase(
                new PdoSAEGroupRepository(),
                new PdoParticipatedInRepository(),
                new PdoSAESubjectRepository()
            );
            $useCase->execute($this->user, $studentId, $groupId);

            SessionService::setFlash('success', 'Étudiant ajouté au groupe.');
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
        return $method === 'POST' && preg_match('/^\/sae\/\d+\/groups\/add-student$/', $path);
    }
}
