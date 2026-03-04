<?php

namespace Controllers\SAE;

use Controllers\BaseController;
use Exception;
use Models\Repository\User\PdoClientRepository;
use Override;
use Views\SAE\CreateSaeView;

/**
 * Controller to display the SAE creation form.
 *
 * @category   Controllers
 * @package    Src
 * @subpackage Controllers/SAE
 * @author     Dinesh Radjou <dinesh.radjou@univ-amu.fr>
 * @license    https://opensource.org/licenses/MIT MIT License
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager/blob/main/App/src/Controllers/SAE/CreateSaeController.php
 */
class CreateSaeController extends BaseController
{
    /**
     * Controls the rendering of the SAE creation form.
     *
     * @return void
     * @throws Exception If an unknown user is encountered.
     */
    public function control(): void
    {
        $this->ensureProfessor();

        $clientInterface = new PdoClientRepository();
        $clients = $clientInterface->findAll();

        $view = new CreateSaeView(['clients' => $clients]);
        $view->render();
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
        return $path === "/sae/create" && $method === "GET";
    }
}
