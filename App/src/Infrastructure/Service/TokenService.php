<?php

namespace App\Infrastructure\Service;

use App\Domain\Auth\Token;
use App\Domain\Auth\IRepository\ITokenRepository;
use App\Infrastructure\Exception\TokenCreationException;
use App\Domain\Auth\Exception\InvalidTokenException;
use App\Domain\User\Exception\SpamException;
use DateTime;

/**
 * Class TokenService
 *
 * This class provides utility functions to manage password reset tokens.
 * It uses a TokenRepository for persistence operations.
 *
 * @category   Services
 * @package    Src
 * @subpackage App/Infrastructure
 * @author     Radjou Dinesh <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class TokenService
{
    private ITokenRepository $tokenRepository;

    public function __construct(ITokenRepository $tokenRepository)
    {
        $this->tokenRepository = $tokenRepository;
    }

    /**
     * Returns a 64-character long secure random hexadecimal string.
     *
     * @return string
     */
    public static function generateRandomTokenValue(): string
    {
        return bin2hex(random_bytes(32)); // 64 hexadecimal characters.
    }

    /**
     * Creates a password reset token for the given email address.
     *
     * This method performs the following actions:
     * - Deletes existing tokens for the email.
     * - Removes expired tokens from the database.
     * - Generates a new secure token.
     * - Stores the token in the `password_resets` table with a 10-minute expiry.
     *
     * @param string $email The email address to associate with the reset token.
     *
     * @return string The newly generated token value.
     *
     * @throws TokenCreationException If a database error occurs.
     * @throws SpamException If too many reset requests are detected for the given email.
     */
    public function createPasswordResetToken(string $email): string
    {
        try {
            // Clean up old tokens for this email.
            $this->tokenRepository->deleteTokensByEmail($email);
            $this->tokenRepository->cleanupExpiredAndUsedTokens();

            // Generate new token.
            $tokenValue = self::generateRandomTokenValue();
            $createdAt = new DateTime();
            $expiresAt = (new DateTime())->modify('+10 minutes');

            $token = new Token($email, $tokenValue, $createdAt, $expiresAt);
            $this->tokenRepository->save($token);

            return $tokenValue;
        }
        catch (\PDOException $e) { // Catch PDOException from repository
            error_log("Erreur création token: " . $e->getMessage());
            // This part is specific to the original error handling, might need adjustment
            if (strpos($e->getMessage(), 'TOO_MANY_RESET_REQUESTS') !== false) {
                error_log("Trop de demandes de réinitialisation pour: {$email}");
                throw new SpamException(
                    "Trop de demandes de réinitialisation. "
                    . "Veuillez réessayer plus tard dans quelques minutes."
                    );
            }
            else {
                throw new TokenCreationException();
            }
        }
    }

    /**
     * Validates the given token and returns associated token entity.
     *
     * @param string $tokenValue The token string to validate.
     *
     * @return Token The token entity if valid.
     *
     * @throws InvalidTokenException If the token is invalid, expired, or already used.
     */
    public function validateToken(string $tokenValue): Token
    {
        // Sanitize the token: allow only hexadecimal characters.
        if (!preg_match('/^[a-f0-9]{64}$/i', $tokenValue)) {
            throw new InvalidTokenException("Ce lien de réinitialisation est invalide.");
        }

        $token = $this->tokenRepository->findByToken($tokenValue);

        if (!$token) {
            throw new InvalidTokenException(
                "Ce lien de réinitialisation est invalide ou a expiré. "
                . "Veuillez faire une nouvelle demande."
                );
        }

        if ($token->isUsed()) {
            throw new InvalidTokenException("Ce lien de réinitialisation a déjà été utilisé.");
        }

        if ($token->isExpired()) {
            throw new InvalidTokenException("Ce lien de réinitialisation a expiré.");
        }

        return $token;
    }

    /**
     * Marks the token as used.
     *
     * @param string $tokenValue The token string to mark as used.
     *
     * @return boolean True if successful, false otherwise.
     */
    public function markTokenAsUsed(string $tokenValue): bool
    {
        return $this->tokenRepository->markAsUsed($tokenValue);
    }
}