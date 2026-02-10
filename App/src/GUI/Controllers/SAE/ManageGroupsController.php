<?php

namespace App\GUI\Controllers\SAE;

use App\GUI\Controllers\BaseController;
use App\Domain\SAE\Exception\AccessDeniedException;
use App\Infrastructure\Service\SessionService;
use App\Application\SAE\CanUserManageSaeUseCase;
use App\Application\SAE\GetSaeDetailsUseCase;
use App\Application\SAE\GetAvailableStudentsForSaeUseCase;
use App\Application\User\GetProfessorsUseCase;
use App\Infrastructure\Persistence\Pdo\PdoSaeRepository;
use App\Infrastructure\Persistence\Pdo\PdoSaeGroupRepository;
use App\Infrastructure\Persistence\Pdo\PdoUserRepository;
use Override;
use App\GUI\Views\SAE\ManageGroupsView;

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
     * @throws AccessDeniedException If the user does not have permission to manage the SAE.
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
            $saeRepo = new PdoSaeRepository();
            $groupRepo = new PdoSaeGroupRepository();

            $canManageUseCase = new CanUserManageSaeUseCase($saeRepo);
            if (!$canManageUseCase->execute($this->user, $sae_id)) {
                throw new AccessDeniedException("Vous n'avez pas la permission de gérer les groupes.");
            }

            $getDetailsUseCase = new GetSaeDetailsUseCase($saeRepo, $groupRepo);
            $saeData = $getDetailsUseCase->execute($sae_id, $this->user);

            $getAvailableStudentsUseCase = new GetAvailableStudentsForSaeUseCase($groupRepo, $saeRepo);
            $availableStudents = $getAvailableStudentsUseCase->execute($this->user, $sae_id);

            $profsAvailable = [];
            if ($this->user->isProfessor()) {
                $useCase = new GetProfessorsUseCase(new PdoUserRepository());
                $profsAvailable = $useCase->execute();
            }

            $view = new ManageGroupsView([
                'user' => $this->user,
                'sae' => $saeData,
                'available_students' => $availableStudents,
                'all_professors' => $profsAvailable,
            ]);
            $view->render();
        } catch (AccessDeniedException $e) {
            SessionService::setFlash('errors', $e->getMessage());
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