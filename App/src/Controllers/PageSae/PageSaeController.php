<?php

namespace Controllers\PageSae;

use Core\ControllerInterface;
use Core\includes\exception\ExceptionDashboard;
use Core\Utilis\SessionService;
use Exception;
use Views\PageSAE\PageSaeView;

/**
 * This class controls the SAE page.

 * @category Controller

 * @package Src

 * @subpackage Controllers\PageSae

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
     */
    public function control(): void
    {
        // Redirect to dashboard if already logged in.
        if (!SessionService::has('user_id')) {
            header('Location: /');
            exit();
        }

        try {
            // Retrieve the user object stored in the session.
            $user = unserialize(SessionService::get('USER'));

            $data['user'] = $user;

            if (!$user) {
                throw new Exception('Utilisateur inconnu');
            }

            $data['saes'] = $user->getSaes();
            $sae_id = basename($_SERVER['REQUEST_URI']);

            foreach ($data['saes'] as $key => $sae) {

                if ($sae->getSaeSubjectId() == $sae_id) {
                    // Create and render the SAE page view.
                    $data['sae'] = $sae;
                    $view = new PageSaeView($data);
                    $view->render();
                    exit();
                }
            }
            header('Location: /');

        } catch (Exception $e) {
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
