<?php

namespace Models\SAE;

/**
 * Represents a SAE (Situations d'Apprentissage et d'Évaluation) subject in the system.
 *
 * @category   Models
 * @package    Src
 * @subpackage Models\SAE
 * @author     Alexandre Benhafessa <alexandre.benhafessa@etu.univ-amu.fr>
 * @author     François Dargentolle <francois.dargentolle@etu.univ-amu.fr>
 * @author     William Edelstein <william.edelstein@etu.univ-amu.fr>
 * @author     Nathan Griguer <nathan.griguer@etu.univ-amu.fr>
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */

class SAE
{
    /**
     * The unique identifier for the SAE subject.
     *
     * @var integer
     */
    private int $sae_subject_id;

    /**
     * The user ID of the responsible professor.
     *
     * @var integer
     */
    private int $responsible_prof_id;

    /**
     * The user ID of the client associated with the SAE.
     *
     * @var integer
     */
    private int $client_id;

    /**
     * The name or title of the SAE subject.
     *
     * @var string
     */
    private string $subject_name;

    /**
     * The start date of the SAE.
     *
     * @var string
     */
    private string $begin_date;

    /**
     * The end date of the SAE.
     *
     * @var string
     */
    private string $end_date;

    /**
     * Constructs a new SAE object.
     *
     * @param array $data An array containing the SAE data, typically fetched from the database.
     */
    protected function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    /**
     * Creates an array of SAE objects from an array of raw SAE data arrays.
     *
     * @param array $saes An array of arrays, where each inner array is raw SAE data.
     *
     * @return array An array of SAE objects.
     */
    public static function createSAEsFromArray(array $saes): array
    {
        $result = [];
        foreach ($saes as $sae) {
            $result[] = new SAE($sae);
        }
        return $result;
    }

    /**
     * Returns the SAE data as a numerically indexed array.
     *
     * @return array The SAE properties in the order: [id, prof_id, client_id, name, begin_date, end_date].
     */
    public function getDataArray(): array
    {
        return[
            $this->sae_subject_id,
            $this->responsible_prof_id,
            $this->client_id,
            $this->subject_name,
            $this->begin_date,
            $this->end_date
        ];
    }


    // ---------------------
    // Getters and Setters
    // ---------------------

    /**
     * Gets the unique identifier for the SAE subject.
     *
     * @return integer
     */
    public function getSaeSubjectId(): int
    {
        return $this->sae_subject_id;
    }

    /**
     * Sets the unique identifier for the SAE subject.
     *
     * @param integer $sae_subject_id The SAE subject ID.
     *
     * @return void
     */
    public function setSaeSubjectId(int $sae_subject_id): void
    {
        $this->sae_subject_id = $sae_subject_id;
    }

    /**
     * Gets the user ID of the responsible professor.
     *
     * @return integer
     */
    public function getResponsibleProfId(): int
    {
        return $this->responsible_prof_id;
    }

    /**
     * Sets the user ID of the responsible professor.
     *
     * @param integer $responsible_prof_id The responsible professor's user ID.
     *
     * @return void
     */
    public function setResponsibleProfId(int $responsible_prof_id): void
    {
        $this->responsible_prof_id = $responsible_prof_id;
    }

    /**
     * Gets the user ID of the client associated with the SAE.
     *
     * @return integer
     */
    public function getClientId(): int
    {
        return $this->client_id;
    }

    /**
     * Sets the user ID of the client associated with the SAE.
     *
     * @param integer $client_id The client's user ID.
     *
     * @return void
     */
    public function setClientId(int $client_id): void
    {
        $this->client_id = $client_id;
    }

    /**
     * Gets the name or title of the SAE subject.
     *
     * @return string
     */
    public function getSubjectName(): string
    {
        return $this->subject_name;
    }

    /**
     * Sets the name or title of the SAE subject.
     *
     * @param string $subject_name The name of the subject.
     *
     * @return void
     */
    public function setSubjectName(string $subject_name): void
    {
        $this->subject_name = $subject_name;
    }

    /**
     * Gets the start date of the SAE.
     *
     * @return string
     */
    public function getBeginDate(): string
    {
        return $this->begin_date;
    }

    /**
     * Sets the start date of the SAE.
     *
     * @param string $begin_date The start date (format is typically YYYY-MM-DD).
     *
     * @return void
     */
    public function setBeginDate(string $begin_date): void
    {
        $this->begin_date = $begin_date;
    }

    /**
     * Gets the end date of the SAE.
     *
     * @return string
     */
    public function getEndDate(): string
    {
        return $this->end_date;
    }

    /**
     * Sets the end date of the SAE.
     *
     * @param string $end_date The end date (format is typically YYYY-MM-DD).
     *
     * @return void
     */
    public function setEndDate(string $end_date): void
    {
        $this->end_date = $end_date;
    }
}
