<?php

namespace App\Domain\SAE\Exception;

use Exception;

/**
 * Base Exception for all SAE related errors.
 * This allows controllers to catch 'SaeException' to handle any SAE logic error.
 *
 * @category Exception
 * @package  Core
 * @subpackage Includes/exception/SAE
 * @author   Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license  MIT License https://opensource.org/licenses/MIT
 * @link     https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class SaeException extends Exception
{
}