<?php

namespace Controllers\PageSae;

use Controllers\ControllerInterface;
use Views\PageSAE\PageSaeView;

/**
 * @category    Controllers
 * @package     Modulessrc

 * @subpackage  Controllers\PageSae

 * @author      Alexandre Benhafessa <alexandre.benhafessa@etu.univ-amu.fr>,
 *              François Dargentolle <francois.dargentolle@etu.univ-amu.fr>,
 *              William Edelstein <william.edelstein@etu.univ-amu.fr>,
 *              Nathan Griguer <nathan.griguer@etu.univ-amu.fr>,
 *              Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>

 * @license    https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0

 * @link      https://github.com/SAEManager/SAEManager

 * This class controls the SAE page.
 */
class PageSaeController implements ControllerInterface
{
    /**
     * Principal manager of the controller.
     *
     * @return void
     */
    public function control(): void
    {
        $view = new PageSaeView();
        $view->render();
    }

    /**
     * Check if this controller can handle the request.
     *
     * @param string $chemin The requested path.
     * @param string $method The HTTP method.
     *
     * @return boolean Is the method get?
     */
    public static function support(string $chemin, string $method): bool
    {
        return $chemin === "/page-sae" && $method === "GET";
    }
}
