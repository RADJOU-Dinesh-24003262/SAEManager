<?php

namespace Core\Includes\Exception\SAE;

/**
 * Thrown when a user tries to perform an action they are not authorized to do.
 * e.g., A student trying to delete an SAE.
 *
 * @category Exception
 * @package  Core
 * @subpackage Includes/exception/SAE
 * @author   Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license  MIT License https://opensource.org/licenses/MIT
 * @link     https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class ExceptionAccessDenied extends ExceptionSAE
{
}
