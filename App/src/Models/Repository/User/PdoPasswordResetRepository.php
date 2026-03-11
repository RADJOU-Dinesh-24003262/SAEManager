<?php

namespace Models\Repository\User;

use Core\Includes\Database;
use Core\Includes\Exception\ExceptionToken\ExceptionCreationTokenFailed;
use Core\Includes\Exception\ExceptionToken\ExceptionInvalidToken;
use Core\Includes\Exception\ExceptionSpam;
use Models\UseCase\User\InterfaceDB\PasswordResetInterface;
use Override;
use PDO;
use PDOException;

/**
 * PDO implementation of PasswordResetTokenInterface.
 *
 * This is the Infrastructure layer implementation of the Interface.
 * It implements the Interface defined in the Use Cases layer.
 *
 * @category   Models
 * @package    Src
 * @subpackage Models/Repository/User
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class PdoPasswordResetRepository implements PasswordResetInterface
{
    private const TABLE = 'password_resets';

    /**
     * @var PDO The database connection.
     */
    protected PDO $connection;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->connection = Database::getInstance();
    }

    /**
     * Inserts a password reset token into the database.
     *
     * Stores the token with the associated email, creation timestamp,
     * expiration date, and marks it as unused.
     *
     * @param string             $email     The email address associated with the reset request.
     * @param string             $token     The generated password reset token.
     * @param \DateTimeImmutable $expiresAt The expiration date and time of the token.
     *
     * @return boolean Returns true on successful insertion, false on failure.
     *
     * @throws ExceptionSpam If too many reset requests are detected for the email address.
     */
    #[Override]
    public function insert(string $email, string $token, \DateTimeImmutable $expiresAt): bool
    {
        try {
            $stmt = $this->connection->prepare(
                'INSERT INTO ' . self::TABLE . ' (email, token, created_at, expires_at, used)
                 VALUES (LOWER(:email), :token, :created_at, :expires_at, FALSE)'
            );

            return $stmt->execute([
                'email'      => $email,
                'token'      => $token,
                'created_at' => date('Y-m-d H:i:s'),
                'expires_at' => $expiresAt->format('Y-m-d H:i:s'),
            ]);
        } catch (PDOException $e) {
            error_log('Erreur insertion token password reset: ' . $e->getMessage());

            if (str_contains($e->getMessage(), 'TOO_MANY_RESET_REQUESTS')) {
                error_log("Trop de demandes de réinitialisation pour: {$email}");
                throw new ExceptionSpam(
                    'Trop de demandes de réinitialisation. '
                    . 'Veuillez réessayer plus tard dans quelques minutes.'
                );
            }

            return false;
        }
    }

    /**
     * Finds token data by its string value.
     *
     * @param string $token The token string.
     *
     * @return array<string, mixed>|null The token data or null if not found.
     */
    #[Override]
    public function findByToken(string $token): ?array
    {
        try {
            $stmt = $this->connection->prepare(
                'SELECT email, expires_at, used
                 FROM ' . self::TABLE . '
                 WHERE token = :token'
            );

            $stmt->execute(['token' => $token]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            return $result ?: null;
        } catch (PDOException $e) {
            error_log('Erreur recherche token: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Marks a token as used.
     *
     * @param string $token The token string.
     *
     * @return boolean True on success, false on failure.
     */
    #[Override]
    public function markAsUsed(string $token): bool
    {
        try {
            $stmt = $this->connection->prepare(
                'UPDATE ' . self::TABLE . '
                 SET used = TRUE
                 WHERE token = :token'
            );

            return $stmt->execute(['token' => $token]);
        } catch (PDOException $e) {
            error_log('Erreur marquage token: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Removes expired and used tokens from the database.
     *
     * @return integer The number of tokens removed.
     */
    #[Override]
    public function purgeExpiredTokens(): int
    {
        try {
            $stmt = $this->connection->prepare(
                'DELETE FROM ' . self::TABLE . '
                 WHERE used = TRUE OR expires_at <= NOW()'
            );

            $stmt->execute();
            return $stmt->rowCount();
        } catch (PDOException $e) {
            error_log('Erreur nettoyage tokens: ' . $e->getMessage());
            return 0;
        }
    }
}
