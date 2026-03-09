<?php
namespace Models\UseCase\User\InterfaceDB;

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
interface PasswordResetInterface
{
    /**
     * Creates a password reset token for the given email address.
     *
     * Deletes existing tokens for the email, removes expired tokens,
     * generates a new secure token, and stores it with a 10-minute expiry.
     *
     * @param string $email The email address to associate with the reset token.
     *
     * @return string The newly generated token.
     *
     * @throws \Core\Includes\Exception\ExceptionToken\ExceptionCreationTokenFailed If a database error occurs.
     * @throws \Core\Includes\Exception\ExceptionSpam                               If too many reset requests are detected.
     */
    public function createPasswordResetToken(string $email): string;

    /**
     * Validates the given token and returns the associated data (email, expires_at, used).
     *
     * @param string $token The token to validate.
     *
     * @return array{email: string, expires_at: string, used: int|bool} The token details if valid.
     *
     * @throws \Core\Includes\Exception\ExceptionToken\ExceptionInvalidToken If the token is invalid, expired, or already used.
     */
    public function validateToken(string $token): array;

    /**
     * Marks the token as used in the database.
     *
     * @param string $token The token to mark as used.
     *
     * @return bool True if successful, false otherwise.
     */
    public function markTokenAsUsed(string $token): bool;

    /**
     * Deletes all expired or used tokens from the database.
     *
     * @return int Number of deleted rows.
     */
    public function purgeExpiredTokens(): int;
}