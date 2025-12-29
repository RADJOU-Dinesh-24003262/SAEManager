<?php

namespace Models\SAE;

use Core\Models\BaseModel;
use Override;

/**
 * Represents a Competence associated with a SAE.
 *
 * Competences define the skills/abilities that students will develop
 * through working on the SAE project.
 *
 * @category   Models
 * @package    Src
 * @subpackage Models/SAE
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class Competence extends BaseModel
{
    /**
     * The name of the competence.
     * @var string
     */
    protected string $competence_name;

    /**
     * The SAE subject ID.
     * @var integer
     */
    protected int $sae_subject_id;

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
     * Validates the competence data.
     *
     * @return array<int, string> Array of validation errors (empty if valid).
     */
    #[Override]
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
     * Converts to array.
     *
     * @return array<string, integer|string>
     */
    #[Override]
    public function toArray(): array
    {
        return [
            'competence_name' => $this->competence_name,
            'sae_subject_id' => $this->sae_subject_id,
        ];
    }

    /**
     * Gets the competence name.
     *
     * @return string
     */
    public function getCompetenceName(): string
    {
        return $this->competence_name;
    }

    /**
     * Sets the competence name.
     *
     * @param string $competence_name The competence name.
     * @return void
     */
    public function setCompetenceName(string $competence_name): void
    {
        $this->competence_name = $competence_name;
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
}
