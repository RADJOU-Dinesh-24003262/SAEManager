<?php

namespace Models\SAE;

/**
 * Represents a SAE Group in the system.
 *
 * A group contains multiple students working together on a SAE project.
 *
 * @category   Models
 * @package    Src
 * @subpackage Models\SAE
 * @author     SAE Manager Team
 * @license    MIT License https://opensource.org/licenses/MIT
 */
class SAEGroup
{
    private ?int $sae_group_id = null;
    private int $sae_subject_id;

    /**
     * Constructor
     *
     * @param array $data Initial data
     */
    public function __construct(array $data = [])
    {
        $this->hydrate($data);
    }

    /**
     * Hydrates the object with data
     *
     * @param array $data Data to hydrate with
     * @return void
     */
    private function hydrate(array $data): void
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    /**
     * Validates the group data
     *
     * @return array Array of validation errors (empty if valid)
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
     * @return array
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
