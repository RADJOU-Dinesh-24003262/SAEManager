<?php
namespace Models\PageSae;

use includes\database;
use PDO;
use PDOException;

class PageSae {

    private string $title = '';
    private string $content = '';
    private string $managerName = '';
    private string $creationDate = '';
    private array  $groups = [];
    private string $finaldate = '';

    private database $db;

    public function __construct(database $db)
    {
        $this->db = $db;
    }

    public function getTitle(): string {
        return $this->title;
    }

    public function getContent(): string {
        return $this->content;
    }

    public function getManagerName(): string {
        return $this->managerName;
    }

    public function getCreationDate(): string {
        return $this->creationDate;
    }

    public function getGroups(): array {
        return $this->groups;
    }

    public function getFinalDate(): string {
        return $this->finaldate;
    }

    public static function createFromData(array $data): self
    {
        $sae = new self(database::getInstance());
        $sae->title = $data['title'] ?? '';
        $sae->content = $data['content'] ?? '';
        $sae->managerName = $data['managerName'] ?? '';
        $sae->creationDate = $data['creationDate'] ?? '';
        $sae->groups = $data['groups'] ?? [];
        $sae->finaldate = $data['finaldate'] ?? '';
        return $sae;
    }

    public static function fetchByAmuid(string $amuid): ?self {
        try {
            $db = database::getInstance();
            $stmt = $db->prepare("SELECT title, content, managerName, creationDate, groups, finaldate FROM sae WHERE amuid = :amuid");
            $stmt->bindParam(':amuid', $amuid, PDO::PARAM_STR);
            $stmt->execute();
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($data) {
                $data['groups'] = json_decode($data['groups'], true) ?? [];
                $data['finaldate'] = $data['finaldate'];
                return new self($data);
            }
            return null;
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return null;
        }
    }

    public static function save(): bool {

        try {
         $stmt = $this->db->prepare("
            INSERT INTO sae (title, content, managerName, creationDate, groups, finaldate)
            VALUES (:title, :content, :managerName, :creationDate, :groups, :finaldate)
            RETURNING (success)
         ");
            $stmt->execute([
                ':title' => $sae->getTitle(),
                ':content' => $sae->getContent(),
                ':managerName' => $sae->getManagerName(),
                ':creationDate' => $sae->getCreationDate(),
                ':groups' => json_encode($sae->getGroups()),
                ':finaldate' => $sae->getFinalDate()
            ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['success'] === true;
        }
        catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return false;
        }
    }
}
