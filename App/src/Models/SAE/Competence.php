<?php

namespace Models\SAE;

/**
 * Represents a Competence associated with a SAE.
 *
 * Competences define the skills/abilities that students will develop
 * through working on the SAE project.
 *
 * @category   Models
 * @package    Src
 * @subpackage Models\SAE
 * @author     SAE Manager Team
 * @license    MIT License https://opensource.org/licenses/MIT
 */
class Competence
{
    private string $competence_name;
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
     * Validates the competence data
     *
     * @return array Array of validation errors (empty if valid)
     */
    public function validate(): array
    {
        $errors = [];

        if (empty($this->competence_name)) {
            $errors[] = 'Le nom de la compétence ne peut pas être vide';
        }

        if (strlen($this->competence_name) > 255) {
            $errors[] = 'Le nom de la compétence ne peut pas dépasser 255 caractères';
        }

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
            'competence_name' => $this->competence_name,
            'sae_subject_id' => $this->sae_subject_id,
        ];
    }

    // Getters and Setters
    public function getCompetenceName(): string
    {
        return $this->competence_name;
    }

    public function setCompetenceName(string $competence_name): void
    {
        $this->competence_name = $competence_name;
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
