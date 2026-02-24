<?php

namespace Models\Entity\User;

use Override;

/**
 * Represents a student user in the system.
 *
 * Contains only business logic and properties.
 * Database operations are handled by StudentRepository.
 *
 * @category   Models
 * @package    Src
 * @subpackage Models/Entity/User
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class Student extends User
{
    /**
     * The AMU identification string.
     *
     * @var string
     */
    protected string $amu_id;

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
    protected string $td;

    /**
     * The TP group of the student.
     *
     * @var string
     */
    protected string $tp;

    /**
     * The student ID.
     *
     * @var integer
     */
    protected int $student_id;

    /**
     * Initializes a new student.
     *
     * @param array<string, string|integer|null> $data The student data.
     */
    public function __construct(array $data = [])
    {
        parent::__construct($data);
        $this->user_type = 'student';
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
     * Gets the student's major.
     *
     * @return string|null
     */
    public function getMajor(): ?string
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

    /**
     * Get the student id in the database of the student
     *
     * @return integer
     */
    public function getStudentId(): int
    {
        return $this->student_id;
    }

    /**
     * Gets the user's role label for display.
     *
     * @return string
     */
    #[Override]
    public function getRoleLabel(): string
    {
        return 'Étudiant';
    }

    /**
     * Gets the student's dashboard meta information for display.
     *
     * @return array<string, mixed>
     */
    #[Override]
    public function getDashboardMetaInfo(): array
    {
        $meta = [
            'Année' => $this->year,
            'Groupe' => $this->td . '-' . $this->tp,
        ];

        if ($this->major) {
            $meta['Parcours'] = $this->major;
        }

        return $meta;
    }
}
