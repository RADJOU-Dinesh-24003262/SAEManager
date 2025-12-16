<?php

namespace Models\SAE;

use DateTime;

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
     * @var integer|null
     */
    private ?int $sae_subject_id = null;

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
     * The file path for SAE documents.
     *
     * @var string|null
     */
    private ?string $file_path = null;

    /**
     * Array of competences associated with this SAE.
     *
     * @var array
     */
    private array $competences = [];

    /**
     * Constructs a new SAE object.
     *
     * @param array $data An array containing the SAE data, typically fetched from the database.
     */
    public function __construct(array $data = [])
    {
        $this->hydrate($data);
    }

    /**
     * Hydrates the object with data from an array.
     *
     * @param array $data The data to hydrate with.
     *
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
     * Creates an array of SAE objects from an array of raw SAE data arrays.
     *
     * @param array $saes An array of arrays, where each inner array is raw SAE data.
     *
     * @return array<SAE> An array of SAE objects.
     */
    public static function createSAEsFromArray(array $saes): array
    {
        $result = [];
        foreach ($saes as $sae) {
            $sae = new SAE($sae);
            $result[] = $sae;
        }
        return $result;
    }

    /**
     * Returns the SAE data as a numerically indexed array.
     *
     * @return array The SAE properties in the order: [id, prof_id, client_id, name, begin_date, end_date, file_path].
     */
    public function getDataArray(): array
    {
        return [
            $this->sae_subject_id,
            $this->responsible_prof_id,
            $this->client_id,
            $this->subject_name,
            $this->begin_date,
            $this->end_date,
            $this->file_path
        ];
    }

    /**
     * Returns the SAE data as an associative array.
     *
     * @return array Associative array of SAE data.
     */
    public function toArray(): array
    {
        return [
            'sae_subject_id' => $this->sae_subject_id,
            'responsible_prof_id' => $this->responsible_prof_id,
            'client_id' => $this->client_id,
            'subject_name' => $this->subject_name,
            'begin_date' => $this->begin_date,
            'end_date' => $this->end_date,
            'file_path' => $this->file_path,
        ];
    }

    /**
     * Checks if the SAE is currently active.
     *
     * @return boolean True if the SAE is active, false otherwise.
     */
    public function isActive(): bool
    {
        $now = new DateTime();
        $begin = new DateTime($this->begin_date);
        $end = new DateTime($this->end_date);

        return $now >= $begin && $now <= $end;
    }

    /**
     * Checks if the SAE has started.
     *
     * @return boolean True if the SAE has started, false otherwise.
     */
    public function hasStarted(): bool
    {
        $now = new DateTime();
        $begin = new DateTime($this->begin_date);

        return $now >= $begin;
    }

    /**
     * Checks if the SAE has ended.
     *
     * @return boolean True if the SAE has ended, false otherwise.
     */
    public function hasEnded(): bool
    {
        $now = new DateTime();
        $end = new DateTime($this->end_date);

        return $now > $end;
    }

    /**
     * Gets the number of days remaining until the end of the SAE.
     *
     * @return integer Number of days remaining (negative if ended).
     */
    public function getDaysRemaining(): int
    {
        $now = new DateTime();
        $end = new DateTime($this->end_date);
        $interval = $now->diff($end);

        if (!$interval->days) {
            return 0;
        }

        return $interval->invert ? -$interval->days : $interval->days;
    }

    /**
     * Gets the duration of the SAE in days.
     *
     * @return integer Number of days the SAE lasts.
     */
    public function getDuration(): int
    {
        $begin = new DateTime($this->begin_date);
        $end = new DateTime($this->end_date);

        if (!$begin->diff($end)->days) {
            return 0;
        }

        return $begin->diff($end)->days;
    }

    /**
     * Validates the SAE data.
     *
     * @return array Array of validation errors (empty if valid).
     */
    public function validate(): array
    {
        $errors = [];

        if (empty($this->subject_name)) {
            $errors[] = 'Le nom du sujet ne peut pas être vide';
        }

        if (strlen($this->subject_name) > 255) {
            $errors[] = 'Le nom du sujet ne peut pas dépasser 255 caractères';
        }

        if ($this->responsible_prof_id <= 0) {
            $errors[] = 'L\'ID du professeur responsable doit être valide';
        }

        if ($this->client_id <= 0) {
            $errors[] = 'L\'ID du client doit être valide';
        }

        try {
            $begin = new DateTime($this->begin_date);
            $end = new DateTime($this->end_date);

            if ($end <= $begin) {
                $errors[] = 'La date de fin doit être après la date de début';
            }
        } catch (\Exception $e) {
            $errors[] = 'Les dates ne sont pas valides';
        }

        return $errors;
    }

    // ---------------------
    // Getters and Setters
    // ---------------------

    /**
     * Gets the unique identifier for the SAE subject.
     *
     * @return integer|null
     */
    public function getSaeSubjectId(): ?int
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
     * @param string $begin_date The start date (format: YYYY-MM-DD).
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
     * @param string $end_date The end date (format: YYYY-MM-DD).
     *
     * @return void
     */
    public function setEndDate(string $end_date): void
    {
        $this->end_date = $end_date;
    }

    /**
     * Gets the file path for SAE documents.
     *
     * @return string|null
     */
    public function getFilePath(): ?string
    {
        return $this->file_path;
    }

    /**
     * Sets the file path for SAE documents.
     *
     * @param string|null $file_path The file path.
     *
     * @return void
     */
    public function setFilePath(?string $file_path): void
    {
        $this->file_path = $file_path;
    }

    /**
     * Gets the competences associated with this SAE.
     *
     * @return array
     */
    public function getCompetences(): array
    {
        return $this->competences;
    }

    /**
     * Sets the competences associated with this SAE.
     *
     * @param array $competences The competences array.
     *
     * @return void
     */
    public function setCompetences(array $competences): void
    {
        $this->competences = $competences;
    }

    /**
     * Adds a competence to this SAE.
     *
     * @param string $competence The competence name.
     *
     * @return void
     */
    public function addCompetence(string $competence): void
    {
        if (!in_array($competence, $this->competences)) {
            $this->competences[] = $competence;
        }
    }

    /**
     * Removes a competence from this SAE.
     *
     * @param string $competence The competence name.
     *
     * @return void
     */
    public function removeCompetence(string $competence): void
    {
        $this->competences = array_filter(
            $this->competences,
            fn($c) => $c !== $competence
        );
    }
}
