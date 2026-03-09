<?php

namespace Controllers\Sae;

use Controllers\BaseController;
use Core\Includes\Exception\ExceptionDashboard;
use Core\Includes\Exception\SAE\ExceptionAccessDenied;
use Core\Includes\Exception\SAE\ExceptionSAE;
use Core\Utils\SessionService;
use Exception;
use Models\Repository\SAE\PdoParticipatedInRepository;
use Models\Repository\SAE\PdoSAEGroupRepository;
use Models\Repository\SAE\PdoSAESubjectRepository;
use Models\Repository\User\PdoClientRepository;
use Models\Repository\User\PdoProfessorRepository;
use Models\Repository\User\PdoStudentRepository;
use Models\UseCase\SAE\GetCompleteSAEDataUseCase;
use Views\Sae\PageSaeView;
use Override;

/**
 * This class controls the SAE page.

 * @category Controller

 * @package Src

 * @subpackage Controllers/PageSae

 * @author Alexandre Benhafessa <alexandre.benhafessa@etu.univ-amu.fr>
 * @author François Dargentolle <francois.dargentolle@etu.univ-amu.fr>
 * @author William Edelstein <william.edelstein@etu.univ-amu.fr>
 * @author Nathan Griguer <nathan.griguer@etu.univ-amu.fr>
 * @author Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>

 * @license MIT License https://opensource.org/licenses/MIT

 * @link https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class SaeController extends BaseController
{
    /**
     * Principal manager of the controller
     *
     * @param integer $saeId The SAE ID.

     * @return void
     * @throws ExceptionAccessDenied If access is denied.
     */
    public function control(int $saeId = 0): void
    {
        $this->ensureAuthenticated();

        try {
            $subjectInterface = new PdoSAESubjectRepository();
            $groupInterface = new PdoSAEGroupRepository();
            $participatedInInterface = new PdoParticipatedInRepository();

            $studentInterface = new PdoStudentRepository();
            $professorInterface = new PdoProfessorRepository();
            $clientInterface = new PdoClientRepository();

            $sae = new GetCompleteSAEDataUseCase(
                $subjectInterface,
                $groupInterface,
                $participatedInInterface,
                $studentInterface,
                $professorInterface,
                $clientInterface
            );

            $saeData = $sae->execute($saeId, $this->user);

            if ($saeData === null) {
                SessionService::setFlash('errors', "SAE introuvable ou accès refusé.");
                header('Location: /dashboard');
                exit();
            }

            // Create and render the SAE page view.
            $view = new PageSaeView(
                $saeData['subject'],
                $saeData['groups'],
                $saeData['responsible_professor'],
                $saeData['all_professors'],
                $saeData['client'],
                $this->user
            );
            $view->render();
            exit();
        } catch (ExceptionAccessDenied $e) {
            SessionService::setFlash('errors', $e->getMessage());
            header('Location: /dashboard');
            exit();
        } catch (ExceptionSAE $e) {
            SessionService::setFlash('errors', "Erreur SAE : " . $e->getMessage());
            header('Location: /dashboard');
            exit();
        } catch (ExceptionDashboard $e) {
            SessionService::setFlash('errors', $e->getMessage());
            header('Location: /dashboard');
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
        return preg_match('/^\/sae\/\d*$/', $path) && strtoupper($method) === 'GET';
    }
}
