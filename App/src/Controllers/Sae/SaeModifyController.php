<?php

namespace Controllers\Sae;

use Controllers\BaseController;
use Core\Utils\SessionService;
use Models\SAE\SAE;
use Models\User\Client;
use Override;
use Views\SAE\ModifySaeView;
use Models\UseCase\SAE\GetCompleteSAEDataUseCase;
use Models\Repository\SAE\PdoSAESubjectRepository;
use Models\Repository\SAE\PdoSAEGroupRepository;
use Models\Repository\SAE\PdoParticipatedInRepository;
use Models\Repository\User\PdoStudentRepository;
use Models\Repository\User\PdoProfessorRepository;
use Models\Repository\User\PdoClientRepository;

/**
 * Controller for displaying the SAE modification form.
 *
 * This class handles the GET request to show the form for editing an existing SAE.
 * It retrieves the necessary SAE data and the list of clients to populate the view.
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
class SaeModifyController extends BaseController
{
    /**
     * Principal manager of the controller.
     *
     * Verifies permissions, fetches SAE data and available clients,
     * and renders the modification view.
     *
     * @param integer $saeId The SAE ID.
     *
     * @return void
     * @throws \Exception If the SAE is not found or an error occurs during data retrieval.
     */
    public function control(int $saeId = 0): void
    {
        $this->ensureProfessor();

        try {
            $useCase = new GetCompleteSAEDataUseCase(
                new PdoSAESubjectRepository(),
                new PdoSAEGroupRepository(),
                new PdoParticipatedInRepository(),
                new PdoStudentRepository(),
                new PdoProfessorRepository(),
                new PdoClientRepository()
            );

            // Retrieve complete SAE data.
            $saeData = $useCase->execute($saeId, $this->user);

            if (!$saeData) {
                throw new \Exception("SAE non trouvée ou accès refusé.");
            }

            $subject = $saeData['subject'];
            if ($subject->getResponsibleProfId() !== $this->user->getUserId()) {
                SessionService::setFlash('errors', ["Vous n'avez pas la permission de modifier cette SAE."]);
                header('Location: /sae/' . $saeId);
                exit;
            }

            // Retrieve the list of clients.
            $clientRepo = new PdoClientRepository();
            $clients = array_map(function ($client) {
                return [
                    'user_id' => $client->getUserId(),
                    'first_name' => $client->getFirstName(),
                    'last_name' => $client->getLastName(),
                    'organisation' => $client->getOrganisation()
                ];
            }, $clientRepo->findAll());

            // Pass data to the view.
            $view = new ModifySaeView(
                $saeData,
                $clients,
                $this->user,
                SessionService::generateCsrfToken()
            );

            $view->render();
        } catch (\Exception $e) {
            SessionService::setFlash('errors', [$e->getMessage()]);
            header('Location: /dashboard');
            exit;
        }
    }

    /**
     * Check if this controller can handle the request.
     *
     * @param string $path   The request path.
     * @param string $method The HTTP request method.
     *
     * @return boolean True if the controller supports the request, otherwise false.
     */
    #[Override]
    public static function support(string $path, string $method): bool
    {
        return preg_match('/^\/sae\/\d+\/modify$/', $path) && $method === "GET";
    }
}
