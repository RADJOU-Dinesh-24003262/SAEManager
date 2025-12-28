<?php

namespace Controllers\PageSae;

use Core\ControllerInterface;
use Core\includes\exception\ExceptionDashboard;
use Core\includes\exception\SAE\ExceptionSAE;
use Core\includes\exception\SAE\ExceptionAccessDenied;
use Core\Utilis\SessionService;
use Exception;
use Models\User\User;
use Views\PageSAE\PageSaeView;
use Models\SAE\SAE;

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
class PageSaeController implements ControllerInterface
{
    /**
     * Principal manager of the controller
     *
     * @return void
     * @throws Exception If the user variable is not as expected.
     * @throws ExceptionAccessDenied If access is denied.
     */
    public function control(): void
    {
        // Redirect to /login if not logged in.
        if (!SessionService::has('user_id')) {
            SessionService::setFlash('errors', ['Authentification requise.']);
            header('Location: /login');
            exit();
        }

        try {
            // Retrieve the user object stored in the session.
            $user = unserialize(SessionService::get('USER'));

            $data['user'] = $user;

            if (!$user || !($user instanceof User)) {
                throw new Exception('Unknown user');
            }

            $sae_id = intval(basename($_SERVER['REQUEST_URI']));

            if (!$user->canAccessSAE($sae_id)) {
                throw new ExceptionAccessDenied("Vous n'avez pas la permission d'accéder à cette SAE.");
            }

            $sae = SAE::getInstance();
            $data['sae'] = $sae->getCompleteSAEData($sae_id, $user);

            // Create and render the SAE page view.
            $view = new PageSaeView($data);
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
    public static function support(string $path, string $method): bool
    {
        return preg_match('/^\/sae\/\d*$/', $path) && strtoupper($method) === 'GET';
    }
}
