<?php

namespace App\GUI\Controllers\SAE;

use App\GUI\Controllers\BaseController;
use App\GUI\Exception\DashboardException;
use App\Domain\SAE\Exception\AccessDeniedException;
use App\Domain\SAE\Exception\SaeException;
use App\Infrastructure\Service\SessionService;
use App\Application\SAE\CanUserAccessSaeUseCase;
use App\Application\SAE\GetSaeDetailsUseCase;
use App\Infrastructure\Persistence\Pdo\PdoSaeRepository;
use App\Infrastructure\Persistence\Pdo\PdoSaeGroupRepository;
use Override;
use App\GUI\Views\PageSAE\PageSaeView;

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
class PageSaeController extends BaseController
{
    /**
     * Principal manager of the controller
     *
     * @return void
     * @throws AccessDeniedException If access is denied.
     */
    #[Override]
    public function control(): void
    {
        $this->ensureAuthenticated();

        try {
            $data['user'] = $this->user;

            $sae_id = intval(basename($_SERVER['REQUEST_URI']));

            $saeRepo = new PdoSaeRepository();
            $accessUseCase = new CanUserAccessSaeUseCase($saeRepo);

            if (!$accessUseCase->execute($this->user, $sae_id)) {
                throw new AccessDeniedException("Vous n'avez pas la permission d'accéder à cette SAE.");
            }

            $detailsUseCase = new GetSaeDetailsUseCase($saeRepo, new PdoSaeGroupRepository());
            $data['sae'] = $detailsUseCase->execute($sae_id, $this->user);

            // Create and render the SAE page view.
            $view = new PageSaeView($data);
            $view->render();
            exit();
        } catch (AccessDeniedException $e) {
            SessionService::setFlash('errors', $e->getMessage());
            header('Location: /dashboard');
            exit();
        } catch (SaeException $e) {
            SessionService::setFlash('errors', "Erreur SAE : " . $e->getMessage());
            header('Location: /dashboard');
            exit();
        } catch (DashboardException $e) {
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