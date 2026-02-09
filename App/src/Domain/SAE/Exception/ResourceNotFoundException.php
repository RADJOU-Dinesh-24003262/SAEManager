<?php

namespace App\Domain\SAE\Exception;

/**
 * Thrown when a requested resource (SAE, Group, etc.) cannot be found in the database.
 *
 * @category Exception
 * @package  Core
 * @subpackage Includes/exception/SAE
 * @author   Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license  MIT License https://opensource.org/licenses/MIT
 * @link     https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class ResourceNotFoundException extends SaeException
{
}