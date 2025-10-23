<?php

namespace Models\User;

use includes\exception\ExceptionFetchDataBD;
use PDO;

/**
 * Represents a student user in the system.
 *
 * @category  Models
 * @package   Src
 * @subpackage Models\User
 * @author    Alexandre Benhafessa <alexandre.benhafessa@etu.univ-amu.fr>
 * @author    François Dargentolle <francois.dargentolle@etu.univ-amu.fr>
 * @author    William Edelstein <william.edelstein@etu.univ-amu.fr>
 * @author    Nathan Griguer <nathan.griguer@etu.univ-amu.fr>
 * @author    Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license   MIT License https://opensource.org/licenses/MIT
 * @link      https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class Student extends User
{
    /**
     * The AMU identification string.
     *
     * @var string
     */
    protected string $amu_id = '';

    /**
     * The year of study of the student.
     *
     * @var integer
     */
    protected int $year;

    /**
     * The major/parcours of the student.
     *
     * @var string|null
     */
    protected ?string $major = '';

    /**
     * The TD group of the student.
     *
     * @var string
     */
    protected string $td = '';

    /**
     * The TP group of the student.
     *
     * @var string
     */
    protected string $tp = '';

    /**
     * Initializes a new student.
     *
     * @param array $data The student data.
     */
    public function __construct(array $data = [])
    {
        $this->user_type = 'student';
        parent::__construct($data);
    }

    /**
     * Saves student-specific data to the database.
     *
     * @param PDO     $connection The database connection.
     * @param integer $userId     The user ID from the users table.
     *
     * @return void
     */
    protected function saveSpecificData(PDO $connection, int $userId): void
    {
        $stmt = $connection->prepare(
            'INSERT INTO students (student_id, amu_id, year, td, tp)
             VALUES (:student_id, :amu_id, :year, :td, :tp)'
        );

        $stmt->execute([
            'student_id' => $userId,
            'amu_id' => $this->amu_id,
            'year' => $this->year,
            'td' => $this->td,
            'tp' => $this->tp,
        ]);
    }

    /**
     * Fetches student-specific data from the database.
     *
     * @param PDO    $db    The database connection.
     * @param string $email The user's email.
     *
     * @return void
     * @throws ExceptionFetchDataBD If the data can't be fetch.
     */
    protected function fetchSpecificData(PDO $db, string $email): void
    {
        $stmt = $db->prepare(
            'SELECT s.*
             FROM students s
             JOIN users u ON s.student_id = u.user_id
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
        } else {
            throw new ExceptionFetchDataBD();
        }
    }

    // -----------------
    // Getters
    // -----------------

    /**
     * Gets the student's AMU ID.
     *
     * @return string
     */
    public function getAmuId(): string
    {
        return $this->amu_id;
    }

    /**
     * Gets the student's year of study.
     *
     * @return integer
     */
    public function getYear(): int
    {
        return $this->year;
    }

    /**
     * Gets the student's parcours (major).
     *
     * @return string|null
     */
    public function getParcours(): ?string
    {
        return $this->major;
    }

    /**
     * Gets the student's TD group.
     *
     * @return string
     */
    public function getTd(): string
    {
        return $this->td;
    }

    /**
     * Gets the student's TP group.
     *
     * @return string
     */
    public function getTp(): string
    {
        return $this->tp;
    }
}
