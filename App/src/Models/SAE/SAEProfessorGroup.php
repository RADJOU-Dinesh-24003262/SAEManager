<?php

namespace Models\SAE;

use Core\Models\BaseModel;
use Override;

/**
 * Represents the association between a Professor and a SAE.
 *
 * This is a junction table model for many-to-many relationship.
 *
 * @category   Models
 * @package    Src
 * @subpackage Models/SAE
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class SAEProfessorGroup extends BaseModel
{
    /**
     * The SAE subject ID.
     * @var integer
     */
    protected int $sae_subject_id;

    /**
     * The professor ID.
     * @var integer
     */
    protected int $professor_id;

    /**
     * Constructor.
     *
     * @param array<string, mixed> $data Initial data.
     */
    public function __construct(array $data = [])
    {
        $this->hydrate($data);
    }

    /**
     * Validates the association data.
     *
     * @return array<int, string> Array of validation errors (empty if valid).
     */
    #[Override]
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
     * Converts to array.
     *
     * @return array<string, int>
     */
    #[Override]
    public function toArray(): array
    {
        return [
            'sae_subject_id' => $this->sae_subject_id,
            'professor_id' => $this->professor_id,
        ];
    }

    /**
     * Gets the SAE subject ID.
     *
     * @return integer
     */
    public function getSaeSubjectId(): int
    {
        return $this->sae_subject_id;
    }

    /**
     * Sets the SAE subject ID.
     *
     * @param integer $sae_subject_id The SAE subject ID.
     * @return void
     */
    public function setSaeSubjectId(int $sae_subject_id): void
    {
        $this->sae_subject_id = $sae_subject_id;
    }

    /**
     * Gets the professor ID.
     *
     * @return integer
     */
    public function getProfessorId(): int
    {
        return $this->professor_id;
    }

    /**
     * Sets the professor ID.
     *
     * @param integer $professor_id The professor ID.
     * @return void
     */
    public function setProfessorId(int $professor_id): void
    {
        $this->professor_id = $professor_id;
    }
}
