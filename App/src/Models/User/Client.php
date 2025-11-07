<?php

namespace Models\User;

use PDO;

/**
 * Represents a client user in the system.
 *
 * @category   Models
 * @package    Src
 * @subpackage Models\User
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
     * @param array $data The client data.
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
     * @return array An array of SAE subjects data.
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
