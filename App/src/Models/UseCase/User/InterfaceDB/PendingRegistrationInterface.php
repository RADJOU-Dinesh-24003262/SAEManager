<?php

namespace Models\UseCase\User\InterfaceDB;

use DateTimeImmutable;

/**
 * Interface for pending_registrations repository.
 *
 * @category   Models
 * @package    Src
 * @subpackage Models/UseCase/User/InterfaceDB
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
interface PendingRegistrationInterface
{
    /**
     * Inserts a new pending registration.
     *
     * @param string            $token     The verification token.
     * @param string            $firstName The user's first name.
     * @param string            $lastName  The user's last name.
     * @param string            $email     The user's email address.
     * @param string            $phone     The user's phone number.
     * @param string            $password  The hashed password.
     * @param string            $status    The user type (student, professor, client).
     * @param DateTimeImmutable $expiresAt The token expiry date.
     * @param string|null       $amuId     AMU identifier (student/professor only).
     * @param string|null       $td        TD group (student only).
     * @param string|null       $tp        TP group (student only).
     * @param string|null       $major     Major (student only).
     * @param integer|null      $year      Year of study (student only).
     *
     * @return boolean True on success, false on failure.
     */
    public function insert(
        string $token,
        string $firstName,
        string $lastName,
        string $email,
        string $phone,
        string $password,
        string $status,
        DateTimeImmutable $expiresAt,
        ?string $amuId = null,
        ?string $td = null,
        ?string $tp = null,
        ?string $major = null,
        ?int $year = null
    ): bool;

    /**
     * Finds a pending registration by token.
     * Returns null if not found, expired, or already used.
     *
     * @param string $token The verification token.
     *
     * @return array<string, mixed>|null The pending registration data or null.
     */
    public function findValidByToken(string $token): ?array;

    /**
     * Checks if a pending registration exists for the given email.
     * Only checks non-expired and unused entries.
     *
     * @param string $email The email address.
     *
     * @return boolean True if a pending registration exists.
     */
    public function existsByEmail(string $email): bool;

    /**
     * Marks a pending registration as used.
     *
     * @param string $token The verification token.
     *
     * @return boolean True on success, false on failure.
     */
    public function markAsUsed(string $token): bool;

    /**
     * Deletes all expired or used pending registrations.
     * To be called periodically to keep the table clean.
     *
     * @return integer Number of deleted rows.
     */
    public function purgeExpired(): int;
}
