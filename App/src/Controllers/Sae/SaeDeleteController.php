<?php

namespace Controllers\Sae;

use Controllers\BaseController;
use Core\Includes\Exception\SAE\ExceptionAccessDenied;
use Core\Utils\SessionService;
use Models\SAE\SAE;
use Override;
use Services\FileService;
use Models\UseCase\SAE\DeleteSAEUseCase;
use Models\Repository\SAE\PdoSAESubjectRepository;

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
class SaeDeleteController extends BaseController
{
    /**
     * Principal manager of the controller
     *
     * @param integer $saeId The SAE ID.
     *
     * @return void
     * @throws \Exception If a general error occurs during the modification process.
     */
    public function control(int $saeId = 0): void
    {
        $this->ensureProfessor();

        try {
            $repository = new PdoSAESubjectRepository();
            $useCase = new DeleteSAEUseCase($repository);

            $useCase->execute($saeId, $this->user);

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
