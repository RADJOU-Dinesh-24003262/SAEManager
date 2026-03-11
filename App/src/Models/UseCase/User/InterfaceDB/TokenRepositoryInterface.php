<?php

namespace Models\UseCase\User\InterfaceDB;

/**
 * Interface for repositories that handle token entities (password resets, pending registrations).
 *
 * @category   Models
 * @package    Src
 * @subpackage Models/UseCase/User/InterfaceDB
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
interface TokenRepositoryInterface
{
    /**
     * Finds token data by its string value.
     *
     * @param string $token The token string.
     * @return array<string, mixed>|null The token data or null if not found.
     */
    public function findByToken(string $token): ?array;

    /**
     * Marks a token as used.
     *
     * @param string $token The token string.
     * @return boolean True on success, false on failure.
     */
    public function markAsUsed(string $token): bool;
}
