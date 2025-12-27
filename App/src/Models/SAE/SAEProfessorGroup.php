<?php

namespace Models\SAE;

use Core\BaseModel;

/**
 * Represents the association between a Professor and a SAE.
 *
 * This is a junction table model for many-to-many relationship.
 *
 * @category   Models
 * @package    Src
 * @subpackage Models/SAE
 * @author     SAE Manager Team
 * @license    MIT License https://opensource.org/licenses/MIT
 */
class SAEProfessorGroup extends BaseModel
{
    protected int $sae_subject_id;
    protected int $professor_id;

    /**
     * Constructor
     *
     * @param array<string, mixed> $data Initial data
     */
    public function __construct(array $data = [])
    {
        $this->hydrate($data);
    }

    /**
     * Validates the association data
     *
     * @return array<int, string> Array of validation errors (empty if valid)
     */
    public function validate(): array
    {
        $errors = [];

        if ($this->sae_subject_id <= 0) {
            $errors[] = 'L\'ID du sujet SAE doit être valide';
        }

        if ($this->professor_id <= 0) {
            $errors[] = 'L\'ID du professeur doit être valide';
        }

        return $errors;
    }

    /**
     * Converts to array
     *
     * @return array<string, int>
     */
    public function toArray(): array
    {
        return [
            'sae_subject_id' => $this->sae_subject_id,
            'professor_id' => $this->professor_id,
        ];
    }

    // Getters and Setters
    public function getSaeSubjectId(): int
    {
        return $this->sae_subject_id;
    }

    public function setSaeSubjectId(int $sae_subject_id): void
    {
        $this->sae_subject_id = $sae_subject_id;
    }

    public function getProfessorId(): int
    {
        return $this->professor_id;
    }

    public function setProfessorId(int $professor_id): void
    {
        $this->professor_id = $professor_id;
    }
}
