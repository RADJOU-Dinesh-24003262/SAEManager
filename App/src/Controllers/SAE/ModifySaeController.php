<?php

namespace Controllers\SAE;

use Controllers\BaseController;
use Core\Utilis\SessionService;
use Models\SAE\SAE;
use Models\User\Client;
use Override;
use Views\SAE\ModifySaeView;

class ModifySaeController extends BaseController
{
    #[Override]
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

        try {
            $sae = SAE::getInstance();

            // 🔑 POINT IMPORTANT 3 : Récupérer les données complètes de la SAE
            $saeData = $sae->getCompleteSAEData($saeId, $this->user);

            if (!$saeData) {
                throw new \Exception("SAE non trouvée");
            }

            // 🔑 POINT IMPORTANT 4 : Récupérer la liste des clients
            $clients = Client::getAllClients();

            // 🔑 POINT IMPORTANT 5 : Passer les données à la vue
            $view = new ModifySaeView([
                'sae' => $saeData,
                'clients' => $clients,
                'user' => $this->user
            ]);

            $view->render();

        } catch (\Exception $e) {
            SessionService::setFlash('errors', [$e->getMessage()]);
            header('Location: /dashboard');
            exit;
        }
    }

    #[Override]
    public static function support(string $path, string $method): bool
    {
        return preg_match('/^\/sae\/\d+\/modify$/', $path) && $method === "GET";
    }
}
?>