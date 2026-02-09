<?php

namespace App\Domain\User;

/**
 * Professor entity representing a university professor.
 * 
 * Professors are responsible for SAE subjects and can supervise student groups.
 *
 * @category Domain
 * @package  App\Domain\User
 * @author   RADJOU Dinesh <dinesh.radjou@etu.univ-amu.fr>
 * @license  MIT License https://opensource.org/licenses/MIT
 * @link     https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class Professor extends User
{
    protected string $amu_id = '';

    /**
     * Professor constructor.
     *
     * @param array $data Associative array with professor data.
     */
    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }

    /**
     * Gets the AMU (Aix-Marseille Université) professor ID.
     *
     * @return string The AMU ID.
     */
    public function getAmuId(): string
    {
        return $this->amu_id;
    }

    /**
     * Gets the professor ID (same as user_id).
     *
     * @return int The professor ID.
     */
    public function getProfessorId(): int
    {
        return $this->user_id ?? 0;
    }
}