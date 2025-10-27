<?php

namespace Controllers\PageSae;

use Core\ControllerInterface;
use Views\PageSAE\PageSaeView;

/**
 * This class controls the SAE page.

 * @category Controller

 * @package Src

 * @subpackage Controllers\PageSae

 * @author  Alexandre Benhafessa <alexandre.benhafessa@etu.univ-amu.fr>
 * @author  François Dargentolle <francois.dargentolle@etu.univ-amu.fr>
 * @author  William Edelstein <william.edelstein@etu.univ-amu.fr>
 * @author  Nathan Griguer <nathan.griguer@etu.univ-amu.fr>
 * @author  Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>

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
        $view = new PageSaeView();
        $view->render();
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
        return $path === '/page-sae' && strtoupper($method) === 'GET';
    }
}
