<?php

namespace Models\SAE;

use Core\BaseModel;

/**
 * Represents a SAE Group in the system.
 *
 * A group contains multiple students working together on a SAE project.
 *
 * @category   Models
 * @package    Src
 * @subpackage Models/SAE
 * @author     SAE Manager Team
 * @license    MIT License https://opensource.org/licenses/MIT
 */
class SAEGroup extends BaseModel
{
    protected ?int $sae_group_id = null;
    protected int $sae_subject_id;

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
     * Validates the group data
     *
     * @return array<int, string> Array of validation errors (empty if valid)
     */
    public function validate(): array
    {
        $errors = [];

        if ($this->sae_subject_id <= 0) {
            $errors[] = 'L\'ID du sujet SAE doit être valide';
        }

        return $errors;
    }

    /**
     * Converts to array
     *
     * @return array<string, integer|null>
     */
    public function toArray(): array
    {
        return [
            'sae_group_id' => $this->sae_group_id,
            'sae_subject_id' => $this->sae_subject_id,
        ];
    }

    // Getters and Setters
    public function getSaeGroupId(): ?int
    {
        return $this->sae_group_id;
    }

    public function setSaeGroupId(int $sae_group_id): void
    {
        $this->sae_group_id = $sae_group_id;
    }

    public function getSaeSubjectId(): int
    {
        return $this->sae_subject_id;
    }

    public function setSaeSubjectId(int $sae_subject_id): void
    {
        $this->sae_subject_id = $sae_subject_id;
    }
}
