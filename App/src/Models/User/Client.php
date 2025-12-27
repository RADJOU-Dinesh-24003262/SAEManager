<?php

namespace Models\User;

use PDO;
use Core\includes\Database;

/**
 * Represents a client user in the system.
 *
 * @category   Models
 * @package    Src
 * @subpackage Models/User
 * @author     Alexandre Benhafessa <alexandre.benhafessa@etu.univ-amu.fr>
 * @author     François Dargentolle <francois.dargentolle@etu.univ-amu.fr>
 * @author     William Edelstein <william.edelstein@etu.univ-amu.fr>
 * @author     Nathan Griguer <nathan.griguer@etu.univ-amu.fr>
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class Client extends User
{
    /**
     * The organisation of the client.
     *
     * @var string
     */
    protected string $organisation = '';

    /**
     * Initializes a new client.
     *
     * @param array<string, string|integer> $data The client data.
     */
    public function __construct(array $data = [])
    {
        $this->user_type = 'client';
        parent::__construct($data);
    }

    /**
     * Saves client-specific data to the database.
     *
     * @param PDO     $connection The database connection.
     * @param integer $userId     The user ID from the users table.
     *
     * @return void
     */
    protected function saveSpecificData(PDO $connection, int $userId): void
    {
        $stmt = $connection->prepare(
            'INSERT INTO clients (client_id, organisation)
             VALUES (:client_id, :organisation)'
        );

        $stmt->execute(
            [
                'client_id' => $userId,
                'organisation' => $this->organisation,
            ]
        );
    }

    /**
     * Fetches client-specific data from the database.
     *
     * @param PDO    $db    The database connection.
     * @param string $email The user's email.
     *
     * @return void
     */
    protected function fetchSpecificData(PDO $db, string $email): void
    {
        $stmt = $db->prepare(
            'SELECT c.*
             FROM clients c
             JOIN users u ON c.client_id = u.user_id
             WHERE u.email = :email'
        );

        $stmt->execute(['email' => $email]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            foreach ($data as $key => $value) {
                if (property_exists($this, $key)) {
                    $this->$key = $value;
                }
            }
        }
    }

    /**
     * Fetches the SAE subjects proposed by this client.
     *
     * @param PDO     $connection The database connection.
     * @param integer $userId     The client's user ID.
     *
     * @return array<int, array{
     *   sae_subject_id: int,
     *   responsible_prof_id: int,
     *   client_id: int,
     *   subject_name: string,
     *   begin_date: string,
     *   end_date: string,
     *   file_path: string|null
     * }> An array of SAE subjects data.
     */
    protected function fetchSAEData(PDO $connection, int $userId): array
    {
        $stmt = $connection->prepare(
            'SELECT * FROM SAE_subjects
             WHERE client_id = :user_id;'
        );

        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * a client can access the SAEs for which they are the client.
     */
    public function canAccessSAE(int $saeId): bool
    {
        try {
            $db = Database::getInstance();
            $stmt = $db->prepare(
                'SELECT COUNT(*) FROM sae_subjects
                 WHERE sae_subject_id = :sae_id AND client_id = :client_id'
            );
            $stmt->execute(['sae_id' => $saeId, 'client_id' => $this->user_id]);
            return $stmt->fetchColumn() > 0;
        } catch (\PDOException $e) {
            error_log('Erreur canAccessSAE (Client) : ' . $e->getMessage());
            return false;
        }
    }

    /**
     * a client can see all students working on THEIR SAEs.
     */
    public function getAccessibleGroupMembers(int $saeId): array
    {
        try {
            $db = Database::getInstance();
            $stmt = $db->prepare(
                'SELECT DISTINCT u.user_id, u.first_name, u.last_name, u.email, u.phone,
                        st.sae_group_id, st.td, st.tp
                 FROM sae_subjects s
                 JOIN sae_groups sg ON s.sae_subject_id = sg.sae_subject_id
                 JOIN students st ON sg.sae_group_id = st.sae_group_id
                 JOIN users u ON st.student_id = u.user_id
                 WHERE s.client_id = :client_id AND s.sae_subject_id = :sae_id
                 ORDER BY st.sae_group_id, u.last_name, u.first_name'
            );
            $stmt->execute(['client_id' => $this->user_id, 'sae_id' => $saeId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log('Erreur getAccessibleGroupMembers (Client) : ' . $e->getMessage());
            return [];
        }
    }

    /**
     * @param integer|null $saeId
     * @return boolean
     */
    public function canManageSAE(?int $saeId = null): bool
    {
        return false;
    }

    /**
     * @param integer $todoId
     * @return boolean
     */
    public function canModifyTodo(int $todoId): bool
    {
        return false;
    }

    // -----------------
    // Getters
    // -----------------

    /**
     * Gets the client's organisation.
     *
     * @return string
     */
    public function getOrganisation(): string
    {
        return $this->organisation;
    }
}
