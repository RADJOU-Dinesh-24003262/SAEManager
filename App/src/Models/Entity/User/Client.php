<?php

namespace Models\Entity\User;

use Override;

/**
 * Represents a client user in the system.
 *
 * Contains only business logic and properties.
 * Database operations are handled by ClientRepository.
 *
 * @category   Models
 * @package    Src
 * @subpackage Models/Entity/User
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class Client extends User
{
    /**
     * The organisation of the client.
     *
     * @var string
     */
    protected string $organisation;

    /**
     * Initializes a new client.
     *
     * @param array<string, string|integer> $data The client data.
     */
    public function __construct(array $data = [])
    {
        parent::__construct($data);
        $this->user_type = 'client';
    }

    // -----------------
    // Getters
    // -----------------

    /**
     * Gets the client's organisation.
     *
     * @return string
     */
    public function getOrganisation(): string
    {
        return $this->organisation;
    }

    /**
     * Gets the user's role label for display.
     *
     * @return string
     */
    #[Override]
    public function getRoleLabel(): string
    {
        return 'Client';
    }
}
