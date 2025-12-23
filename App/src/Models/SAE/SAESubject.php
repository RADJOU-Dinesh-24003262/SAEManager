<?php

namespace Models\SAE;

use DateTime;

/**
 * Represents a SAE Subject (project) in the system.
 *
 * This is the main entity representing a SAE project.
 * It replaces the old SAE.php to follow one-model-per-table principle.
 *
 * @category   Models
 * @package    Src
 * @subpackage Models\SAE
 * @author     SAE Manager Team
 * @license    MIT License https://opensource.org/licenses/MIT
 */
class SAESubject
{
    private ?int $sae_subject_id = null;
    private int $responsible_prof_id;
    private int $client_id;
    private string $subject_name;
    private string $begin_date;
    private string $end_date;
    private ?string $file_path = null;

    /**
     * Constructor
     *
     * @param array<string, mixed> $data Initial data
     */
    public function __construct(array $data = [])
    {
        $this->hydrate($data);
    }

    /**
     * Hydrates the object with data
     *
     * @param array<string, mixed> $data Data to hydrate with
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
     * Validates the SAE subject data
     *
     * @return array<int, string> Array of validation errors (empty if valid)
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
     * Checks if the SAE is currently active
     *
     * @return boolean
     */
    public function isActive(): bool
    {
        $now = new DateTime();
        $begin = new DateTime($this->begin_date);
        $end = new DateTime($this->end_date);

        return $now >= $begin && $now <= $end;
    }

    /**
     * Gets the number of days remaining
     *
     * @return integer
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
     * Converts to array
     *
     * @return array<string, mixed>
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

    // Getters and Setters
    public function getSaeSubjectId(): ?int
    {
        return $this->sae_subject_id;
    }

    public function setSaeSubjectId(int $sae_subject_id): void
    {
        $this->sae_subject_id = $sae_subject_id;
    }

    public function getResponsibleProfId(): int
    {
        return $this->responsible_prof_id;
    }

    public function setResponsibleProfId(int $responsible_prof_id): void
    {
        $this->responsible_prof_id = $responsible_prof_id;
    }

    public function getClientId(): int
    {
        return $this->client_id;
    }

    public function setClientId(int $client_id): void
    {
        $this->client_id = $client_id;
    }

    public function getSubjectName(): string
    {
        return $this->subject_name;
    }

    public function setSubjectName(string $subject_name): void
    {
        $this->subject_name = $subject_name;
    }

    public function getBeginDate(): string
    {
        return $this->begin_date;
    }

    public function setBeginDate(string $begin_date): void
    {
        $this->begin_date = $begin_date;
    }

    public function getEndDate(): string
    {
        return $this->end_date;
    }

    public function setEndDate(string $end_date): void
    {
        $this->end_date = $end_date;
    }

    public function getFilePath(): ?string
    {
        return $this->file_path;
    }

    public function setFilePath(?string $file_path): void
    {
        $this->file_path = $file_path;
    }
}
