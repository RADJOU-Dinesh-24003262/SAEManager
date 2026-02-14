<?php

namespace Models\Entity\SAE;

use Core\Models\BaseModel;
use Override;

/**
 * Represents a participation entry in a SAE Group.
 *
 * Links a student to a SAE Group.
 *
 * @category   Models
 * @package    Src
 * @subpackage Models/Entity/SAE
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
}
