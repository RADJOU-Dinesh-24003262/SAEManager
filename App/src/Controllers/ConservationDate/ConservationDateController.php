<?php

namespace Controllers\ConservationDate;

use Views\ConservationDate\ConservationDateView;
use Core\Controllers\ControllerInterface;
use Override;

/**
 * This class controls the configuration of the conservation date page.
 *
 * @category   Controllers
 * @package    Src
 * @subpackage Controllers/ConservationDate
 * @author     Alexandre Benhafessa <alexandre.benhafessa@etu.univ-amu.fr>
 * @author     François Dargentolle <francois.dargentolle@etu.univ-amu.fr>
 * @author     William Edelstein <william.edelstein@etu.univ-amu.fr>
 * @author     Nathan Griguer <nathan.griguer@etu.univ-amu.fr>
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class ConservationDateController implements ControllerInterface
{
    /**
     * Principal manager of the controller
     *
     * @return void
     */
    #[Override]
    public function control(): void
    {
        $view = new ConservationDateView();
        $view->render();
    }

    /**
     * Check if this controller can handle the request
     *
     * @param  string $path   The requested URI path.
     * @param  string $method The HTTP method used in the request.
     * @return boolean True if the path is "/conservation-date" and the method is GET.
     */
    #[Override]
    public static function support(string $path, string $method): bool
    {
        return $path === "/conservation-date" && $method === "GET";
    }
}
