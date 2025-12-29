<?php

namespace Controllers\Index;

use Core\Controllers\ControllerInterface;
use Views\Index\IndexView;

/**
 * This class controls the Index page.
 *
 * @category   Controllers
 * @package    Src
 * @subpackage Controllers/Index;
 * @author     Alexandre Benhafessa <alexandre.benhafessa@etu.univ-amu.fr>
 * @author     François Dargentolle <francois.dargentolle@etu.univ-amu.fr>
 * @author     William Edelstein <william.edelstein@etu.univ-amu.fr>
 * @author     Nathan Griguer <nathan.griguer@etu.univ-amu.fr>
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 *
 * @link https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class IndexController implements ControllerInterface
{
    /**
     * Principal manager of the controller
     *
     * @return void
     */
    #[\Override]
    public function control(): void
    {
        $view = new IndexView();
        $view->render();
    }

    /**
     * Check if this controller can handle the request
     *
     * @param string $path   The requested URI path.
     * @param string $method The HTTP method used in the request.
     *
     * @return boolean True if the path is "/index" or "/" and the method is GET.
     */
    #[\Override]
    public static function support(string $path, string $method): bool
    {
        return ($path === "/index" || $path === "/") && $method === "GET";
    }
}
