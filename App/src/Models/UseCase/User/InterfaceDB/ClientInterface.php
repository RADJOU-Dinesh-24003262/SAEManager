<?php

namespace Models\UseCase\User\InterfaceDB;

use Models\Entity\User\Client;
use Core\Models\UseCase\InterfaceDB\RepositoryInterface;

/**
 * Interface for Client repository operations.
 *
 * Defines the contract for client-specific data access.
 * This is an Interface (Clean Architecture) - interface defined in Use Cases layer.
 *
 * @category   Models
 * @package    Src
 * @subpackage Models/UseCase/User/InterfaceDB
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 * @extends RepositoryInterface<Client>
 */
interface ClientInterface extends RepositoryInterface, RoleAccessInterface
{
    /**
     * Finds all clients.
     *
     * @return array<Client> Array of client entities.
     */
    public function findAll(): array;
}
