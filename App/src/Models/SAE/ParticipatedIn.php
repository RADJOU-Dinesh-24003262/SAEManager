<?php

namespace Models\SAE;

use Core\Models\BaseModel;
use Override;

/**
 * Represents a participation entry in a SAE Group.
 *
 * Links a student to a SAE Group and Subject.
 *
 * @category   Models
 * @package    Src
 * @subpackage Models/SAE
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class ParticipatedIn extends BaseModel
{
    /**
     * The Student ID.
     * @var integer
     */
    protected int $student_id;

    /**
     * The SAE Group ID.
     * @var integer
     */
    protected int $sae_group_id;

    /**
     * The SAE Subject ID.
     * @var integer|null
     */
    protected ?int $sae_subject_id = null;

    /**
     * Converts to array.
     *
     * @return array<string, integer|null>
     */
    #[Override]
    public function toArray(): array
    {
        return [
            'student_id' => $this->student_id,
            'sae_group_id' => $this->sae_group_id,
            'sae_subject_id' => $this->sae_subject_id,
        ];
    }

    /**
     * Gets the Student ID.
     *
     * @return integer
     */
    public function getStudentId(): int
    {
        return $this->student_id;
    }

    /**
     * Sets the Student ID.
     *
     * @param integer $student_id The Student ID.
     * @return void
     */
    public function setStudentId(int $student_id): void
    {
        $this->student_id = $student_id;
    }

    /**
     * Gets the SAE Group ID.
     *
     * @return integer
     */
    public function getSaeGroupId(): int
    {
        return $this->sae_group_id;
    }

    /**
     * Sets the SAE Group ID.
     *
     * @param integer $sae_group_id The SAE Group ID.
     * @return void
     */
    public function setSaeGroupId(int $sae_group_id): void
    {
        $this->sae_group_id = $sae_group_id;
    }

    /**
     * Gets the SAE Subject ID.
     *
     * @return integer|null
     */
    public function getSaeSubjectId(): ?int
    {
        return $this->sae_subject_id;
    }

    /**
     * Sets the SAE Subject ID.
     *
     * @param integer|null $sae_subject_id The SAE Subject ID.
     * @return void
     */
    public function setSaeSubjectId(?int $sae_subject_id): void
    {
        $this->sae_subject_id = $sae_subject_id;
    }
}
