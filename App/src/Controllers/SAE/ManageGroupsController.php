<?php

namespace Controllers\SAE;

use Controllers\BaseController;
use Core\includes\exception\SAE\ExceptionAccessDenied;
use Core\Utilis\SessionService;
use Models\SAE\SAE;
use Models\User\Professor;
use Override;
use Views\SAE\ManageGroupsView;
use Exception;

/**
 * Controller to display the group management page.
 *
 * @category   Controllers
 * @package    Src
 * @subpackage Controllers/SAE
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class ManageGroupsController extends BaseController
{
    /**
     * Controls the rendering of the group management page.
     *
     * @return void
     * @throws ExceptionAccessDenied If the user does not have permission to manage the SAE.
     */
    #[Override]
    public function control(): void
    {
        $this->ensureProfessor();

        $path = (string) (parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '');
        if (preg_match('/^\/sae\/(\d+)\/groups$/', $path, $matches)) {
            $sae_id = intval($matches[1]);
        } else {
            header('Location: /dashboard');
            exit;
        }

        try {
            $sae = SAE::getInstance();

            if (!$this->user->canManageSAE($sae_id)) {
                throw new ExceptionAccessDenied("Vous n'avez pas la permission de gérer les groupes.");
            }

            $saeData = $sae->getCompleteSAEData($sae_id, $this->user);
            $availableStudents = $sae->getAvailableStudents($this->user, $sae_id);

            $profsAvailable = [];
            if ($this->user instanceof Professor) {
                $profsAvailable = $this->user->getAllProfessors();
            }

            $view = new ManageGroupsView([
                'user' => $this->user,
                'sae' => $saeData,
                'available_students' => $availableStudents,
                'all_professors' => $profsAvailable,
            ]);
            $view->render();
        } catch (ExceptionAccessDenied $e) {
            SessionService::setFlash('error', $e->getMessage());
            header('Location: /sae/' . $sae_id);
            exit;
        }
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
        return preg_match('/^\/sae\/\d+\/groups$/', $path) && $method === "GET";
    }
}
