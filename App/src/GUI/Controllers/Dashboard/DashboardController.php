<?php

namespace App\GUI\Controllers\Dashboard;

use App\Domain\User\Client;
use App\Domain\User\Professor;
use App\Domain\User\Student;
use App\GUI\Controllers\BaseController;
use App\GUI\Exception\DashboardException;
use App\Domain\SAE\Exception\SaeException;
use App\Infrastructure\Service\SessionService;
use App\Application\SAE\GetUserSaesUseCase;
use App\Infrastructure\Persistence\Pdo\PdoSaeRepository;
use Override;
use App\GUI\Views\Dashboard\DashboardView;
use App\GUI\Presenters\DashboardPresenter;

/**
 * Controller responsible for handling the dashboard page.
 *
 * This class implements the ControllerInterface and provides
 * the logic required to render the dashboard view for an authenticated user.
 *
 * If the user is not authenticated (i.e., no user ID exists in the session),
 * they are redirected to the login page with a flash error message.
 *
 * @category   Controllers
 * @package    Src
 * @subpackage Controllers/Dashboard
 *
 * @author  Alexandre Benhafessa <alexandre.benhafessa@etu.univ-amu.fr>
 * @author  François Dargentolle <francois.dargentolle@etu.univ-amu.fr>
 * @author  William Edelstein <william.edelstein@etu.univ-amu.fr>
 * @author  Nathan Griguer <nathan.griguer@etu.univ-amu.fr>
 * @author  Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license MIT https://opensource.org/licenses/MIT
 * @link    https://github.com/RADJOU-Dinesh-24003262/SAEManager/
 */
class DashboardController extends BaseController
{
    /**
     * Handles the logic to display the dashboard.
     *
     * This method:
     * - Checks if the user is authenticated via the session;
     * - Redirects to the login page with an error message if not;
     * - Retrieves the user object from the session;
     * - Passes the user data to the DashboardView and renders it.
     *
     * @throws DashboardException If user data is not found or invalid.
     *
     * @return void
     * @throws DashboardException If the data if empty.
     */
    #[Override]
    public function control(): void
    {
        $this->ensureAuthenticated();

        try {
            $saeRepo = new PdoSaeRepository();
            $useCase = new GetUserSaesUseCase($saeRepo);
            $saes = $useCase->execute($this->user);
            $user = $this->user;

            // Use Presenter to prepare data for the view
            $presenter = new DashboardPresenter();
            $data = $presenter->present($user, $saes);

            // Create and render the dashboard view.
            $view = new DashboardView($data);
            $view->render();
        } catch (DashboardException $e) {
            SessionService::setFlash('errors', $e->getMessage());
            header('Location: /');
            exit();
        } catch (SaeException $e) {
            SessionService::setFlash('errors', "Erreur SAE : " . $e->getMessage());
            header('Location: /');
            exit();
        }
    }

    /**
     * Determines if this controller should handle the current request.
     *
     * This static method checks whether the controller supports
     * a given route path and HTTP method.
     *
     * @param string $path   The URL path of the request (e.g., "/dashboard").
     * @param string $method The HTTP method used (e.g., "GET", "POST").
     *
     * @return boolean Returns true if the path is "/dashboard" and the method is "GET"; otherwise, false.
     */
    #[Override]
    public static function support(string $path, string $method): bool
    {
        return $path === "/dashboard" && $method === "GET";
    }
}