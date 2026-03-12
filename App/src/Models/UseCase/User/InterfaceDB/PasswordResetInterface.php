<?php

namespace Models\UseCase\User\InterfaceDB;

use Core\Includes\Exception\ExceptionSpam;

/**
 * Interface for password_resets token repository.
 *
 * @category   Models
 * @package    Src
 * @subpackage Models/UseCase/User/InterfaceDB
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
interface PasswordResetInterface extends TokenRepositoryInterface
{
    /**
     * Inserts a new password reset token.
     *
     * @param string             $email     The email address.
     * @param string             $token     The generated secure token.
     * @param \DateTimeImmutable $expiresAt The expiration date.
     *
     * @return boolean True on success, false on failure.
     *
     * @throws ExceptionSpam If too many reset requests are detected.
     */
    public function insert(string $email, string $token, \DateTimeImmutable $expiresAt): bool;

}