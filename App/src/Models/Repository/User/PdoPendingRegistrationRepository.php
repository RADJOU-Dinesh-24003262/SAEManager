<?php

namespace Models\Repository\User;

use Core\Includes\Database;
use Models\UseCase\User\InterfaceDB\PendingRegistrationInterface;
use Override;
use PDO;
use PDOException;
use DateTimeImmutable;

/**
 * PDO implementation of PendingRegistrationInterface.
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
class PdoPendingRegistrationRepository implements PendingRegistrationInterface
{
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
    #[Override]
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
    ): bool {
        $query = "INSERT INTO pending_registrations 
                    (token, first_name, last_name, email, phone, password, status, expires_at,
                     amu_id, td, tp, major, year)
                  VALUES 
                    (:token, :first_name, :last_name, :email, :phone, :password, :status, :expires_at,
                     :amu_id, :td, :tp, :major, :year)";

        $this->connection->beginTransaction();
        try {
            $stmt = $this->connection->prepare($query);
            $stmt->bindValue(':token', $token);
            $stmt->bindValue(':first_name', $firstName);
            $stmt->bindValue(':last_name', $lastName);
            $stmt->bindValue(':email', strtolower($email));
            $stmt->bindValue(':phone', $phone);
            $stmt->bindValue(':password', $password);
            $stmt->bindValue(':status', $status);
            $stmt->bindValue(':expires_at', $expiresAt->format('Y-m-d H:i:sP'));
            $stmt->bindValue(':amu_id', $amuId, PDO::PARAM_STR);
            $stmt->bindValue(':td', $td, PDO::PARAM_STR);
            $stmt->bindValue(':tp', $tp, PDO::PARAM_STR);
            $stmt->bindValue(':major', $major, PDO::PARAM_STR);
            $stmt->bindValue(':year', $year, PDO::PARAM_INT);

            $result = $stmt->execute();
            $this->connection->commit();
            return $result;
        } catch (PDOException $e) {
            if ($this->connection->inTransaction()) {
                $this->connection->rollBack();
            }
            error_log('Error inserting pending registration: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Finds a pending registration by token.
     * Returns null if not found, expired, or already used.
     *
     * @param string $token The verification token.
     *
     * @return array<string, mixed>|null The pending registration data or null.
     */
    #[Override]
    public function findValidByToken(string $token): ?array
    {
        try {
            $stmt = $this->connection->prepare(
                // Doit changer les champs pour avoir ceux de user
                'SELECT * FROM pending_registrations
                 WHERE token = :token
                   AND used = false
                   AND expires_at > now()'
            );
            $stmt->execute(['token' => $token]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            return $data ?: null;
        } catch (PDOException $e) {
            error_log('Error in findValidByToken: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Checks if a pending registration exists for the given email.
     * Only checks non-expired and unused entries.
     *
     * @param string $email The email address.
     *
     * @return boolean True if a pending registration exists.
     */
    #[Override]
    public function existsByEmail(string $email): bool
    {
        try {
            $stmt = $this->connection->prepare(
                'SELECT COUNT(*) FROM pending_registrations
                 WHERE email = LOWER(:email)
                   AND used = false
                   AND expires_at > now()'
            );
            $stmt->execute(['email' => $email]);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log('Error checking pending email existence: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Marks a pending registration as used.
     *
     * @param string $token The verification token.
     *
     * @return boolean True on success, false on failure.
     */
    #[Override]
    public function markAsUsed(string $token): bool
    {
        try {
            $stmt = $this->connection->prepare(
                'UPDATE pending_registrations SET used = true WHERE token = :token'
            );
            return $stmt->execute(['token' => $token]);
        } catch (PDOException $e) {
            error_log('Error marking pending registration as used: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Deletes all expired or used pending registrations.
     * To be called periodically to keep the table clean.
     *
     * @return integer Number of deleted rows.
     */
    #[Override]
    public function purgeExpired(): int
    {
        try {
            $stmt = $this->connection->prepare(
                'DELETE FROM pending_registrations
                 WHERE used = true OR expires_at <= now()'
            );
            $stmt->execute();
            return $stmt->rowCount();
        } catch (PDOException $e) {
            error_log('Error purging expired pending registrations: ' . $e->getMessage());
            return 0;
        }
    }
}
