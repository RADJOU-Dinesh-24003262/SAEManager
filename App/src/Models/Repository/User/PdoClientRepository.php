<?php

namespace Models\Repository\User;

use Core\includes\Database;
use Models\Entity\User\Client;
use Models\Entity\User\User;
use Models\UseCase\User\InterfaceDB\ClientInterface;
use Override;
use PDO;
use PDOException;

/**
 * PDO implementation of ClientInterface.
 *
 * This is the Infrastructure layer implementation of the Interface.
 *
 * @category   Models
 * @package    Src
 * @subpackage Models/Repository/User
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 *
 */
class PdoClientRepository implements ClientInterface
{
    /**
     * The User repository for base user operations.
     *
     * @var PdoUserRepository
     */
    private PdoUserRepository $userRepository;

    /**
     * The database connection.
     *
     * @var PDO
     */
    private PDO $connection;



    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->userRepository = new PdoUserRepository();
        $this->connection = Database::getInstance();
    }


    /**
     * Finds a client by ID.
     *
     * @param integer $id The client ID.
     * @return Client|null The client entity or null if not found.
     */
    public function findById(int $id): ?Client
    {

        return $this->userRepository->findById($id);
    }

    /**
     * Finds a client by email.
     *
     * @param string $email The client's email.
     * @return Client|null The client entity or null if not found.
     */
    public function findByEmail(string $email): ?Client
    {
        return $this->userRepository->findByEmail($email);
    }

    /**
     * Inserts a new client into the database.
     *
     * @param object $client The client entity to create.
     * @return integer|boolean The id of created user or false on failure.
     */
    public function insert(object $client): int|bool
    {
        if (!$client instanceof Client) {
            return false;
        }

        $userId = $this->userRepository->insert($client);
        if (!$userId) {
            return false;
        }

        $this->connection->beginTransaction();

        try {
            $stmt = $this->connection->prepare(
                'INSERT INTO clients (client_id, organisation) 
                 VALUES (:client_id, :organisation)'
            );
            $stmt->execute([
                'client_id' => $userId,
                'organisation' => $client->getOrganisation()
            ]);
            $stmt->closeCursor();

            $this->connection->commit();

            return $userId;
        } catch (PDOException $e) {
            $this->connection->rollBack();
            error_log('Error creating client: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Finds all clients.
     *
     * @return array<Client> Array of client entities.
     */
    public function findAll(): array
    {
        try {
            $stmt = $this->connection->prepare(
                'SELECT u.*, c.* 
                 FROM users u
                 JOIN clients c ON u.user_id = c.client_id
                 ORDER BY u.last_name, u.first_name'
            );
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return array_map(fn($data) => new Client($data), $results);
        } catch (PDOException $e) {
            error_log("Error in findAll (Client): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Checks if a client can access a SAE.
     *
     * @param integer $clientId The client ID.
     * @param integer $saeId    The SAE ID.
     * @return boolean True if accessible, false otherwise.
     */
    public function canAccessSAE(int $clientId, int $saeId): bool
    {
        try {
            $stmt = $this->connection->prepare(
                'SELECT COUNT(*) FROM sae_subjects 
                 WHERE sae_subject_id = :sae_id AND client_id = :client_id'
            );
            $stmt->execute(['sae_id' => $saeId, 'client_id' => $clientId]);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log('Error in canAccessSAE (Client): ' . $e->getMessage());
            return false;
        }
    }


    /**
     * Updates an existing client.
     *
     * @param User $client The client entity to update.
     * @return boolean True on success, false on failure.
     */
    public function update(object $client): bool
    {
        if (!$client instanceof Client) {
            return false;
        }

        if (!$this->userRepository->update($client)) {
            return false;
        }

        try {
            $stmt = $this->connection->prepare(
                'UPDATE clients 
                 SET organisation = :organisation
                 WHERE client_id = :id'
            );

            return $stmt->execute([
                'organisation' => $client->getOrganisation(),
                'id' => $client->getUserId()
            ]);
        } catch (PDOException $e) {
            error_log('Error updating client: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Checks if a client exists by email.
     *
     * @param string $email The email to check.
     * @return boolean True if exists, false otherwise.
     */
    public function existsByEmail(string $email): bool
    {
        return $this->userRepository->existsByEmail($email);
    }

    /**
     * Updates a client's password.
     *
     * @param integer $userId       The user ID.
     * @param string  $passwordHash The new hashed password.
     * @return boolean True on success, false on failure.
     */
    public function updatePassword(int $userId, string $passwordHash): bool
    {
        return $this->userRepository->updatePassword($userId, $passwordHash);
    }


    public function delete(int $id): bool
    {
        $this->connection->beginTransaction();
        try {
            $stmt = $this->connection->prepare("DELETE FROM clients WHERE client_id = :id");
            $stmt->execute(['id' => $id]);

            $this->userRepository->delete($id);

            $this->connection->commit();
            return true;
        } catch (PDOException $e) {
            $this->connection->rollBack();
            return false;
        }
    }
}
