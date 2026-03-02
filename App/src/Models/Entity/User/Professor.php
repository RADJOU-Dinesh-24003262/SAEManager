<?php

namespace Models\Entity\User;

use Override;

/**
 * Represents a professor user in the system.
 *
 * Contains only business logic and properties.
 * Database operations are handled by ProfessorRepository.
 *
 * @category   Models
 * @package    Src
 * @subpackage Models/Entity/User
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class Professor extends User
{
    /**
     * AMU identifier of the professor.
     *
     * @var string
     */
    protected string $amu_id;

    /**
     * Initializes a new professor.
     *
     * @param array<string, string|integer> $data Optional initial data for the professor.
     */
    public function __construct(array $data = [])
    {
        parent::__construct($data);
        $this->user_type = 'professor';
    }


    // -----------------
    // Getters
    // -----------------

    /**
     * Returns the professor's AMU identifier.
     *
     * @return string AMU identifier.
     */
    public function getAmuId(): string
    {
        return $this->amu_id;
    }

    /**
     * Gets the user's role label for display.
     *
     * @return string
     */
    #[Override]
    public function getRoleLabel(): string
    {
        return 'Professeur';
    }

    /**
     * Gets the professor's dashboard meta information for display.
     *
     * @return array<string, mixed>
     */
    #[Override]
    public function getDashboardMetaInfo(): array
    {
        return [
            'Département' => 'Informatique',
        ];
    }
}
