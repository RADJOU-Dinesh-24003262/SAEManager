<?php

namespace Models\SAE;

use Core\BaseModel;
use DateTime;

/**
 * Represents a SAE Subject (project) in the system.
 *
 * This is the main entity representing a SAE project.
 *
 * @category   Models
 * @package    Src
 * @subpackage Models/SAE
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class SAESubject extends BaseModel
{
    /**
     * The SAE subject ID.
     *
     * @var integer|null
     */
    protected ?int $sae_subject_id = null;

    /**
     * The responsible professor ID.
     *
     * @var integer
     */
    protected int $responsible_prof_id;

    /**
     * The client ID.
     *
     * @var integer
     */
    protected int $client_id;

    /**
     * The subject name.
     *
     * @var string
     */
    protected string $subject_name;

    /**
     * The begin date.
     *
     * @var string
     */
    protected string $begin_date;

    /**
     * The end date.
     *
     * @var string
     */
    protected string $end_date;

    /**
     * The file path for the subject description.
     *
     * @var string|null
     */
    protected ?string $file_path = null;

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
     * Validates the SAE subject data.
     *
     * @return array<int, string> Array of validation errors (empty if valid).
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

    /**
     * Checks if the SAE is currently active.
     *
     * @return boolean True if active, false otherwise.
     */
    public function isActive(): bool
    {
        $now = new DateTime();
        $begin = new DateTime($this->begin_date);
        $end = new DateTime($this->end_date);

        return $now >= $begin && $now <= $end;
    }

    /**
     * Gets the number of days remaining.
     *
     * @return integer Number of days.
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
     * Converts to array.
     *
     * @return array<string, mixed> The array representation.
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

    // -----------------
    // Getters and Setters
    // -----------------

    /**
     * Gets the SAE subject ID.
     *
     * @return integer|null
     */
    public function getSaeSubjectId(): ?int
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
     * Gets the responsible professor ID.
     *
     * @return integer
     */
    public function getResponsibleProfId(): int
    {
        return $this->responsible_prof_id;
    }

    /**
     * Sets the responsible professor ID.
     *
     * @param integer $responsible_prof_id The responsible professor ID.
     * @return void
     */
    public function setResponsibleProfId(int $responsible_prof_id): void
    {
        $this->responsible_prof_id = $responsible_prof_id;
    }

    /**
     * Gets the client ID.
     *
     * @return integer
     */
    public function getClientId(): int
    {
        return $this->client_id;
    }

    /**
     * Sets the client ID.
     *
     * @param integer $client_id The client ID.
     * @return void
     */
    public function setClientId(int $client_id): void
    {
        $this->client_id = $client_id;
    }

    /**
     * Gets the subject name.
     *
     * @return string
     */
    public function getSubjectName(): string
    {
        return $this->subject_name;
    }

    /**
     * Sets the subject name.
     *
     * @param string $subject_name The subject name.
     * @return void
     */
    public function setSubjectName(string $subject_name): void
    {
        $this->subject_name = $subject_name;
    }

    /**
     * Gets the begin date.
     *
     * @return string
     */
    public function getBeginDate(): string
    {
        return $this->begin_date;
    }

    /**
     * Sets the begin date.
     *
     * @param string $begin_date The begin date.
     * @return void
     */
    public function setBeginDate(string $begin_date): void
    {
        $this->begin_date = $begin_date;
    }

    /**
     * Gets the end date.
     *
     * @return string
     */
    public function getEndDate(): string
    {
        return $this->end_date;
    }

    /**
     * Sets the end date.
     *
     * @param string $end_date The end date.
     * @return void
     */
    public function setEndDate(string $end_date): void
    {
        $this->end_date = $end_date;
    }

    /**
     * Gets the file path.
     *
     * @return string|null
     */
    public function getFilePath(): ?string
    {
        return $this->file_path;
    }

    /**
     * Sets the file path.
     *
     * @param string|null $file_path The file path.
     * @return void
     */
    public function setFilePath(?string $file_path): void
    {
        $this->file_path = $file_path;
    }
}
