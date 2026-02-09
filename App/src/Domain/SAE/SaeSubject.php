<?php

namespace App\Domain\SAE;

use App\Domain\User\Client;
use App\Domain\User\Professor;
use Core\Models\BaseEntity;
use DateTime;

/**
 * SAE (Situation d'Apprentissage et d'Évaluation) Subject entity.
 * 
 * Represents a project-based learning activity with a responsible professor,
 * optional client, and date range.
 *
 * @category Domain
 * @package  App\Domain\SAE
 * @author   SAEManager Team
 * @license  MIT License https://opensource.org/licenses/MIT
 * @link     https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class SaeSubject extends BaseEntity
{
    private ?int $id;
    private int $responsibleProfessorId;
    private ?int $clientId;
    private string $name;
    private string $beginDate;
    private string $endDate;
    private ?string $descriptionFilePath;

    // Object references
    private ?Professor $responsibleProfessor = null;
    private ?Client $client = null;
    /** @var SaeGroup[] */
    private array $groups = [];

    public function __construct(
        int $responsibleProfessorId,
        string $name,
        string $beginDate,
        string $endDate,
        ?int $clientId = null,
        ?string $descriptionFilePath = null,
        ?int $id = null
        )
    {
        $this->responsibleProfessorId = $responsibleProfessorId;
        $this->name = $name;
        $this->beginDate = $beginDate;
        $this->endDate = $endDate;
        $this->clientId = $clientId;
        $this->descriptionFilePath = $descriptionFilePath;
        $this->id = $id;
    }

    /**
     * Checks if the SAE is currently active (within date range).
     *
     * @return bool True if active, false otherwise.
     */
    public function isActive(): bool
    {
        $now = new DateTime();
        $begin = new DateTime($this->beginDate);
        $end = new DateTime($this->endDate);

        return $now >= $begin && $now <= $end;
    }

    /**
     * Gets the total duration in days.
     *
     * @return int Number of days between begin and end dates.
     */
    public function getDuration(): int
    {
        $begin = new DateTime($this->beginDate);
        $end = new DateTime($this->endDate);
        return $begin->diff($end)->days;
    }

    /**
     * Checks if the SAE has started.
     *
     * @return bool True if current date is after begin date.
     */
    public function hasStarted(): bool
    {
        return new DateTime() >= new DateTime($this->beginDate);
    }

    /**
     * Checks if the SAE has ended.
     *
     * @return bool True if current date is after end date.
     */
    public function hasEnded(): bool
    {
        return new DateTime() > new DateTime($this->endDate);
    }

    /**
     * Gets the completion percentage based on time elapsed.
     *
     * @return int Percentage (0-100).
     */
    public function getCompletionPercentage(): int
    {
        $begin = new DateTime($this->beginDate);
        $end = new DateTime($this->endDate);
        $now = new DateTime();

        if ($now < $begin) {
            return 0;
        }
        if ($now > $end) {
            return 100;
        }

        $total = $begin->diff($end)->days;
        $elapsed = $begin->diff($now)->days;

        return $total > 0 ? (int)(($elapsed / $total) * 100) : 0;
    }

    /**
     * @return SaeGroup[]
     */
    public function getGroups(): array
    {
        return $this->groups;
    }

    public function setGroups(array $groups): void
    {
        $this->groups = $groups;
    }

    // Object Accessors
    public function getResponsibleProfessor(): ?Professor
    {
        return $this->responsibleProfessor;
    }

    public function setResponsibleProfessor(Professor $professor): void
    {
        $this->responsibleProfessor = $professor;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): void
    {
        $this->client = $client;
    }


    // Getters and Setters (Existing + IDs)
    public function getId(): ?int
    {
        return $this->id;
    }
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getResponsibleProfessorId(): int
    {
        return $this->responsibleProfessorId;
    }
    public function setResponsibleProfessorId(int $id): void
    {
        $this->responsibleProfessorId = $id;
    }

    public function getClientId(): ?int
    {
        return $this->clientId;
    }
    public function setClientId(?int $id): void
    {
        $this->clientId = $id;
    }

    public function getName(): string
    {
        return $this->name;
    }
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getBeginDate(): string
    {
        return $this->beginDate;
    }
    public function setBeginDate(string $date): void
    {
        $this->beginDate = $date;
    }

    public function getEndDate(): string
    {
        return $this->endDate;
    }
    public function setEndDate(string $date): void
    {
        $this->endDate = $date;
    }

    /**
     * Gets the number of days remaining until the end date.
     *
     * @return int Number of days remaining (can be negative if ended).
     */
    public function getDaysRemaining(): int
    {
        $end = new DateTime($this->endDate);
        $now = new DateTime();
        return $end->diff($now)->days;
    }

    public function getDescriptionFilePath(): ?string
    {
        return $this->descriptionFilePath;
    }
    public function setDescriptionFilePath(?string $path): void
    {
        $this->descriptionFilePath = $path;
    }

    public function toArray(): array
    {
        return [
            'sae_subject_id' => $this->id,
            'responsible_prof_id' => $this->responsibleProfessorId,
            'client_id' => $this->clientId,
            'subject_name' => $this->name,
            'begin_date' => $this->beginDate,
            'end_date' => $this->endDate,
            'file_path' => $this->descriptionFilePath
        ];
    }
}