<?php

namespace Models\Repository\User;

use DEPTRAC_INTERNAL\PhpParser\Node\Stmt\Use_;
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
 * @extends <PdoUserRepository>
 */
class PdoClientRepository extends PdoUserRepository implements ClientInterface
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->entityClass = Client::class;
    }

    /**
     * Finds a client by ID.
     *
     * @param integer $id The client ID.
     * @return Client|null The client entity or null if not found.
     */
    #[Override]
    public function findById(int $id): ?Client
    {
        $data = parent::findByIdUser($id);
            return new Client($data);
    }

    /**
     * Finds a client by email.
     *
     * @param string $email The client's email.
     * @return Client|null The client entity or null if not found.
     */
    public function findByEmail(string $email): ?Client
    {
        $data = parent::findByEmailUser($email);
            return new Client($data);
    }

    /**
     * Creates a new client in the database.
     *
     * @param User $client The client entity to create.
     * @return Client|boolean The created client entity or false on failure.
     */
    #[Override]
    public function create(User $client): Client|bool
    {
        $userId = parent::createUser($client);
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

            return $this->findById($userId);
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
    #[Override]
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
}
