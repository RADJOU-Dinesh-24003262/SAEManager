<?php
namespace Models\PageSae;

use includes\database;
use PDO;
use PDOException;
/**
 * Class PageSae
 * Represents a SAE page with its associated data and provides methods to interact with the database.
 * @package Models\PageSae
 * @author Dargentolle François
 * @version 1.1 (next to come shortly)
 * @see database
 * @category Model
 */
class PageSae {
    
    /** 
     * @var string $title used to get the title of the SAE
     * @var string $content used to get the subject of the SAE
     * @var string $managerName used to get the name of the manager
     * @var string $creationDate used to get the creation date
     * @var array $groups used to get the groups associated with the SAE
     * @var string $finaldate used to get the final date
     * @var database $db used to interact with the database
    */
    private string $title = '';
    private string $content = '';
    private string $managerName = '';
    private string $creationDate = '';
    private array  $groups = [];
    private string $finaldate = '';
    private database $db;
   
    /**
     * @method __construct(database $db) Constructor to initialize the PageSae with a database connection.
     * @param database $db The database connection instance.
     */
    public function __construct(database $db)
    {
        $this->db = $db;
    }

    /**
     * @method getters for all attributes of the class PageSae
     * @return mixed the value of the attribute
     */
    public function getTitle(): string {return $this->title;}
    public function getContent(): string {return $this->content;}
    public function getManagerName(): string {return $this->managerName;}
    public function getCreationDate(): string {return $this->creationDate;}
    public function getGroups(): array {return $this->groups;}
    public function getFinalDate(): string {return $this->finaldate;}

    /**
     * @method static createFromData(array $data) Creates a PageSae instance from an associative array of data.
     * @param array $data The associative array containing SAE data.
     */
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

    /**
     * @method static fetchByAmuid(string $amuid) Fetches a PageSae instance from the database by the AMUID of the user.
     * @param string $amuid The AMUID of the user to fetch.
     */
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

    /**
     * @method save() Saves the current PageSae instance to the database.
     * @return static bool true if the save was successful, false otherwise.
     */
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
