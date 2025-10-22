<?php

namespace Controllers;

/**
 * This class is the interface to be implemented for all the controllers.
 * @category Controllers
 * @package Src
 * @subpackage Controllers

 * @author  Alexandre Benhafessa <alexandre.benhafessa@etu.univ-amu.fr>
 * @author  François Dargentolle <francois.dargentolle@etu.univ-amu.fr>
 * @author  William Edelstein <william.edelstein@etu.univ-amu.fr>
 * @author  Nathan Griguer <nathan.griguer@etu.univ-amu.fr>
 * @author  Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>

 * @license MIT License https://opensource.org/licenses/MIT

 * @link https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */

interface ControllerInterface
{
    /**
     * Principal manager of the controller
     *
     * @return void
     */
    public function control(): void;

    /**
     * Check if this controller can handle the request
     * @param string $path   The request path.
     * @param string $method The HTTP request method.
     * @return boolean Is the method post?
     */
    public static function support(string $path, string $method): bool;
}
