<?php

namespace Controllers\SAE;

use Controllers\BaseController;
use Core\Utilis\SessionService;
use Models\SAE\SAE;
use Models\User\Client;
use Override;
use Views\SAE\ModifySaeView;

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
class ModifySaeController extends BaseController
{
    /**
     * Principal manager of the controller.
     *
     * Verifies permissions, fetches SAE data and available clients,
     * and renders the modification view.
     *
     * @return void
     * @throws \Exception If the SAE is not found or an error occurs during data retrieval.
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

        if (!$this->user->canManageSAE($saeId)) {
            SessionService::setFlash('errors', ["Vous n'avez pas la permission de modifier cette SAE."]);
            header('Location: /sae/' . $saeId);
            exit;
        }

        try {
            $sae = SAE::getInstance();

            // Retrieve complete SAE data.
            $saeData = $sae->getCompleteSAEData($saeId, $this->user);

            if (!$saeData) {
                throw new \Exception("SAE non trouvée");
            }

            // Retrieve the list of clients.
            $clients = Client::getAllClients();

            // Pass data to the view.
            $view = new ModifySaeView([
                'sae' => $saeData,
                'clients' => $clients,
                'user' => $this->user,
                'csrf_token' => SessionService::generateCsrfToken()
            ]);

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
