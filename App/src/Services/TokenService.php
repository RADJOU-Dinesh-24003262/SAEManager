<?php

namespace Services;

use Core\includes\Database;
use Core\includes\exception\ExceptionToken\ExceptionCreationTokenFailed;
use Core\includes\exception\ExceptionToken\ExceptionInvalidToken;
use Core\includes\exception\ExceptionSpam;
use PDO;
use PDOException;

/**
 * Class TokenService
 *
 * This class regroups the functions to manage reset password tokens.
 * It can generate, update, delete, and check the validity of tokens.
 * Requires email integration to function properly.
 *
 * @category   Services
 * @package    Src
 * @subpackage App/Services
 * @author     Radjou Dinesh <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class TokenService
{
    /**
     * Returns a 64-character long secure random hexadecimal string.
     *
     * @return string
     */
    public static function generate(): string
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
     * @return string The newly generated token.
     *
     * @throws ExceptionCreationTokenFailed If a database error occurs.
     * @throws ExceptionSpam If too many reset requests are detected for the given email.
     */
    public static function createPasswordResetToken(string $email): string
    {
        try {
            $db = Database::getInstance();

            // Clean up old tokens for this email.
            self::cleanupExpiredTokens();

            // Generate new token.
            $token = self::generate();
            $createdAt = date('Y-m-d H:i:s');
            $expiresAt = date('Y-m-d H:i:s', strtotime('+10 minutes'));

            $stmt = $db->prepare(
                "
                INSERT INTO password_resets (email, token, created_at, expires_at, used)
                VALUES (LOWER(:email), :token, :created_at, :expires_at, FALSE)
            "
            );

            $stmt->execute(
                [
                'email' => $email,
                'token' => $token,
                'created_at' => $createdAt,
                'expires_at' => $expiresAt
                ]
            );

            return $token ? $token : throw new ExceptionCreationTokenFailed();
        } catch (PDOException $e) {
            error_log("Erreur création token: " . $e->getMessage());

            if (strpos($e->getMessage(), 'TOO_MANY_RESET_REQUESTS') !== false) {
                error_log("Trop de demandes de réinitialisation pour: {$email}");
                throw new ExceptionSpam(
                    "Trop de demandes de réinitialisation. "
                    . "Veuillez réessayer plus tard dans quelques minutes."
                );
            } else {
                throw new ExceptionCreationTokenFailed();
            }
        }
    }

    /**
     * Validates the given token and returns associated user data (email).
     *
     * @param string $token The token to validate.
     *
     * @return array{email: string, expires_at: string, used: int|bool} The token details if valid.
     *
     * @throws ExceptionInvalidToken If the token is invalid, expired, or already used.
     * @throws PDOException If a database error occurs.
     */
    public static function validateToken(string $token): array
    {
        try {
            // Sanitize the token: allow only hexadecimal characters.
            if (!preg_match('/^[a-f0-9]{64}$/i', $token)) {
                throw new ExceptionInvalidToken("Ce lien de réinitialisation est invalide.");
            }

            $db = Database::getInstance();

            $stmt = $db->prepare(
                "
                SELECT email, expires_at, used 
                FROM password_resets 
                WHERE token = :token
            "
            );

            if (!$stmt) {
                throw new PDOException("Failed to prepare statement: " . implode(" ", $db->errorInfo()));
            }

            $stmt->execute(['token' => $token]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$result || !is_array($result)) {
                throw new ExceptionInvalidToken(
                    "Ce lien de réinitialisation est invalide ou a expiré. "
                    . "Veuillez faire une nouvelle demande."
                );
            }

            if ($result['used']) {
                throw new ExceptionInvalidToken("Ce lien de réinitialisation a déjà été utilisé.");
            }

            if (strtotime($result['expires_at']) < time()) {
                throw new ExceptionInvalidToken("Ce lien de réinitialisation a expiré.");
            }

            return [
                    'email'      => (string) $result['email'],
                    'expires_at' => (string) $result['expires_at'],
                    'used'       => (bool) $result['used'],
                ];
        } catch (PDOException $e) {
            error_log("Erreur validation token: " . $e->getMessage());
            throw new ExceptionInvalidToken("Erreur lors de la validation du lien. Veuillez réessayer plus tard.");
        }
    }

    /**
     * Marks the token as used in the database.
     *
     * @param string $token The token to mark as used.
     *
     * @return boolean True if successful, false otherwise.
     */
    public static function markTokenAsUsed(string $token): bool
    {
        try {
            $db = Database::getInstance();

            $stmt = $db->prepare(
                "
                UPDATE password_resets 
                SET used = TRUE 
                WHERE token = :token
            "
            );

            return $stmt->execute(['token' => $token]);
        } catch (PDOException $e) {
            error_log("Erreur marquage token: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Deletes all expired or used tokens from the database.
     *
     * @return void
     */
    public static function cleanupExpiredTokens(): void
    {
        try {
            $db = Database::getInstance();

            $stmt = $db->prepare(
                "
                DELETE FROM password_resets 
                WHERE expires_at < NOW() OR used = TRUE
            "
            );

            $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erreur nettoyage global: " . $e->getMessage());
        }
    }
}
