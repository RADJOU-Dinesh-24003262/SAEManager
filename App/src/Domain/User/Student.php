<?php

namespace App\Domain\User;

/**
 * Student entity representing a university student.
 * 
 * Students are enrolled in academic years (1, 2, or 3 for BUT),
 * belong to TD and TP groups, and may have a chosen major.
 *
 * @category Domain
 * @package  App\Domain\User
 * @author   RADJOU Dinesh <dinesh.radjou@etu.univ-amu.fr>
 * @license  MIT License https://opensource.org/licenses/MIT
 * @link     https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class Student extends User
{
    protected string $amu_id = '';
    protected int $year;
    protected ?string $major = '';
    protected string $td = '';
    protected string $tp = '';
    protected int $student_id;

    /**
     * Student constructor.
     *
     * @param array $data Associative array with student data.
     */
    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }

    /**
     * Gets the AMU (Aix-Marseille Université) student ID.
     *
     * @return string The AMU ID (e.g., "a123456").
     */
    public function getAmuId(): string
    {
        return $this->amu_id;
    }

    /**
     * Gets the academic year (1, 2, or 3 for BUT).
     *
     * @return int The academic year.
     */
    public function getYear(): int
    {
        return $this->year;
    }

    /**
     * Gets the chosen major/parcours (A or B).
     * Only available for years 2 and 3.
     *
     * @return string|null The major, or null if not set.
     */
    public function getMajor(): ?string
    {
        return $this->major;
    }

    /**
     * Gets the TD (Travaux Dirigés) group (TD1, TD2, TD3, or TD4).
     *
     * @return string The TD group identifier.
     */
    public function getTd(): string
    {
        return $this->td;
    }

    /**
     * Gets the TP (Travaux Pratiques) group (TPA or TPB).
     *
     * @return string The TP group identifier.
     */
    public function getTp(): string
    {
        return $this->tp;
    }

    /**
     * Gets the student ID (same as user_id).
     *
     * @return int The student ID.
     */
    public function getStudentId(): int
    {
        return $this->student_id ?? $this->user_id ?? 0;
    }

    /**
     * Checks if the student can choose a major.
     * Majors are available only for years 2 and 3.
     *
     * @return bool True if can choose major, false otherwise.
     */
    public function canChooseMajor(): bool
    {
        return $this->year >= 2;
    }

    /**
     * Checks if the student is in their first year.
     *
     * @return bool True if first year student, false otherwise.
     */
    public function isFirstYear(): bool
    {
        return $this->year === 1;
    }

    /**
     * Checks if the student is in their final year.
     *
     * @return bool True if final year (year 3), false otherwise.
     */
    public function isFinalYear(): bool
    {
        return $this->year === 3;
    }
}