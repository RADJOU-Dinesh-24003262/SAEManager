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
     * Returns a 64-character long secure random hexadecimal string.
     *
     * @return string
     */
    private function generate(): string
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function createPasswordResetToken(string $email): string
    {
        try {
            $this->purgeExpiredTokens();

            $token     = $this->generate();
            $createdAt = date('Y-m-d H:i:s');
            $expiresAt = date('Y-m-d H:i:s', strtotime('+10 minutes'));

            $stmt = $this->connection->prepare(
                'INSERT INTO ' . self::TABLE . ' (email, token, created_at, expires_at, used)
                 VALUES (LOWER(:email), :token, :created_at, :expires_at, FALSE)'
            );

            $stmt->execute([
                'email'      => $email,
                'token'      => $token,
                'created_at' => $createdAt,
                'expires_at' => $expiresAt,
            ]);

            return $token ?: throw new ExceptionCreationTokenFailed();
        } catch (PDOException $e) {
            error_log('Erreur création token: ' . $e->getMessage());

            if (str_contains($e->getMessage(), 'TOO_MANY_RESET_REQUESTS')) {
                error_log("Trop de demandes de réinitialisation pour: {$email}");
                throw new ExceptionSpam(
                    'Trop de demandes de réinitialisation. '
                    . 'Veuillez réessayer plus tard dans quelques minutes.'
                );
            }

            throw new ExceptionCreationTokenFailed();
        }
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function validateToken(string $token): array
    {
        try {
            if (!preg_match('/^[a-f0-9]{64}$/i', $token)) {
                throw new ExceptionInvalidToken('Ce lien de réinitialisation est invalide.');
            }

            $stmt = $this->connection->prepare(
                'SELECT email, expires_at, used
                 FROM ' . self::TABLE . '
                 WHERE token = :token'
            );

            $stmt->execute(['token' => $token]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            if (!$result) {
                throw new ExceptionInvalidToken(
                    'Ce lien de réinitialisation est invalide ou a expiré. '
                    . 'Veuillez faire une nouvelle demande.'
                );
            }

            if ($result['used']) {
                throw new ExceptionInvalidToken('Ce lien de réinitialisation a déjà été utilisé.');
            }

            if (strtotime($result['expires_at']) < time()) {
                throw new ExceptionInvalidToken('Ce lien de réinitialisation a expiré.');
            }

            return $result;
        } catch (PDOException $e) {
            error_log('Erreur validation token: ' . $e->getMessage());
            throw new ExceptionInvalidToken('Erreur lors de la validation du lien. Veuillez réessayer plus tard.');
        }
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function markTokenAsUsed(string $token): bool
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
     * {@inheritDoc}
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
