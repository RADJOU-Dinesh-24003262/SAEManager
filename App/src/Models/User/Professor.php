<?php

namespace Models\User;

use PDO;

/**
 * Represents a professor user in the system.
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
class Professor extends User
{
    /**
     * The AMU identification string.
     *
     * @var string
     */
    protected string $amu_id = '';

    /**
     * Initializes a new professor.
     *
     * @param array $data The professor data.
     */
    public function __construct(array $data = [])
    {
        $this->user_type = 'professor';
        parent::__construct($data);
    }

    /**
     * Saves professor-specific data to the database.
     *
     * @param PDO     $connection The database connection.
     * @param integer $userId     The user ID from the users table.
     *
     * @return void
     */
    protected function saveSpecificData(PDO $connection, int $userId): void
    {
        $stmt = $connection->prepare(
            'INSERT INTO professors (professor_id, amu_id)
             VALUES (:professor_id, :amu_id)'
        );

        $stmt->execute(
            [
            'professor_id' => $userId,
            'amu_id' => $this->amu_id,
            ]
        );
    }

    /**
     * Fetches professor-specific data from the database.
     *
     * @param PDO    $db    The database connection.
     * @param string $email The user\'s email.
     *
     * @return void
     */
    protected function fetchSpecificData(PDO $db, string $email): void
    {
        $stmt = $db->prepare(
            'SELECT p.*
             FROM professors p
             JOIN users u ON p.professor_id = u.user_id
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

    // -----------------
    // Getters
    // -----------------

    /**
     * Gets the professor\'s AMU ID.
     *
     * @return string
     */
    public function getAmuId(): string
    {
        return $this->amu_id;
    }
}
