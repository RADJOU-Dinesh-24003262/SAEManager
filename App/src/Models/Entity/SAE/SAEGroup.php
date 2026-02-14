<?php

namespace Models\Entity\SAE;

use Core\Models\BaseModel;
use Override;

/**
 * Represents a SAE Group.
 *
 * A group contains multiple students working together on a SAE project.
 *
 * @category   Models
 * @package    Src
 * @subpackage Models/Entity/SAE
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class SAEGroup extends BaseModel
{
    /**
     * The SAE group ID.
     * @var integer|null
     */
    protected ?int $sae_group_id = null;

    /**
     * The SAE subject ID.
     * @var integer
     */
    protected int $sae_subject_id;

    /**
     * The professor ID who manages the group.
     * @var integer|null
     */
    protected ?int $professor_id = null;


    /**
     * Gets the SAE group ID.
     *
     * @return integer|null
     */
    public function getSaeGroupId(): ?int
    {
        return $this->sae_group_id;
    }

    /**
     * Sets the SAE group ID.
     *
     * @param integer $sae_group_id The SAE group ID.
     * @return void
     */
    public function setSaeGroupId(int $sae_group_id): void
    {
        $this->sae_group_id = $sae_group_id;
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
     * @return integer|null
     */
    public function getProfessorId(): ?int
    {
        return $this->professor_id;
    }

    /**
     * Sets the professor ID.
     *
     * @param integer|null $professor_id The professor ID.
     * @return void
     */
    public function setProfessorId(?int $professor_id): void
    {
        $this->professor_id = $professor_id;
    }
}
