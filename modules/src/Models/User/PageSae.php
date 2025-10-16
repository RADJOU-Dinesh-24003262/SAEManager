<?php
namespace Models\User;

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

    private function __construct(array $data = []) {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
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

    public static function fetchByAmuid(string $amuid): ?self {
        try {
            $db = database::getInstance();
            $stmt = $db->prepare("SELECT title, content, managerName, creationDate, groups, finaldate FROM sae WHERE amuid = :amuid");
            $stmt->bindParam(':amuid', $amuid, PDO::PARAM_STR);
            $stmt->execute();
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($data) {
                $data['groups'] = json_decode($data['groups'], true) ?? [];
                $data['finaldate'] = $data(['finaldate']);
                return new self($data);
            }
            return null;
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return null;
        }
    }

    public static function save(): bool {
       $connection = database::getInstance();

         $stmt = $connection->prepare("
              SELECT * FROM sae(title = :title,
                                content = :content,
                                managerName = :managerName,
                                creationDate = :creationDate,
                                groups = :groups,
                                finaldate = :finaldate);
         ");
            $stmt->execute([
                ':title' => $this->title,
                ':content' => $this->content,
                ':managerName' => $this->managerName,
                ':creationDate' => $this->creationDate,
                ':groups' => json_encode($this->groups),
                ':finaldate' => $this->finaldate
            ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['success'] === true;
    }
}
