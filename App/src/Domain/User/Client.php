<?php

namespace App\Domain\User;

/**
 * Client entity representing an external client/company.
 * 
 * Clients can commission SAE projects for students to work on.
 *
 * @category Domain
 * @package  App\Domain\User
 * @author   ADJOU Dinesh <dinesh.radjou@etu.univ-amu.fr>
 * @license  MIT License https://opensource.org/licenses/MIT
 * @link     https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class Client extends User
{
    protected string $organisation = '';

    /**
     * Client constructor.
     *
     * @param array $data Associative array with client data.
     */
    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }

    /**
     * Gets the organisation/company name.
     *
     * @return string The organisation name.
     */
    public function getOrganisation(): string
    {
        return $this->organisation;
    }

    /**
     * Sets the organisation/company name.
     *
     * @param string $organisation The organisation name.
     * @return void
     */
    public function setOrganisation(string $organisation): void
    {
        $this->organisation = $organisation;
    }

    /**
     * Gets the client ID (same as user_id).
     *
     * @return int The client ID.
     */
    public function getClientId(): int
    {
        return $this->user_id ?? 0;
    }

    /**
     * Checks if the client has an organisation name set.
     *
     * @return bool True if organisation is set, false otherwise.
     */
    public function hasOrganisation(): bool
    {
        return !empty($this->organisation);
    }
}