<?php

namespace Controllers\SAE;

use Controllers\BaseController;
use Core\includes\exception\SAE\ExceptionAccessDenied;
use Core\Utilis\SessionService;
use Models\SAE\SAE;
use Override;

/**
 * This class controls the deletion of an SAE via GET request.
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
class DeleteSaeController extends BaseController
{
    /**
     * Principal manager of the controller
     *
     * @return void
     * @throws \Exception If a general error occurs during the modification process.
     */
    #[Override]
    public function control(): void
    {
        $this->ensureAuthenticated();
        $user = $this->user;
        $this->ensureProfessor();
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        if (!is_string($path)) {
            $path = '';
        }

        if (preg_match('/^\/sae\/(\d+)\/delete$/', $path, $matches)) {
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
            SAE::getInstance()->deleteSAE($user, $saeId);

            header('Location: /dashboard');
            SessionService::setFlash('success', 'SAE supprimée avec succès');
            exit();
        } catch (\Exception $e) {
            SessionService::setFlash('errors', ['Erreur : ' . $e->getMessage()]);
            header('Location: /sae/' . $saeId . '/modify');
            exit;
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
        return preg_match('/^\/sae\/\d+\/delete$/', $path) && $method === "GET";
    }
}
