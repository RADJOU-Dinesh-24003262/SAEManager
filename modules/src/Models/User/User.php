<?php
namespace Models\User;

use includes\database;
use PDO;
use PDOException;

class User
{
    private ?int $id = null;
    private string $amuId;
    private string $firstName;
    private string $lastName;
    private string $gender;
    private string $userType;
    private string $email;
    private string $passwordHash;
    private string $phone;
    private string $dateOfBirth;
    private string $city;
    private ?int $year = null;
    private ?string $parcours = null;
    private ?string $td = null;
    private ?string $tp = null;
    private string $clearpassword;
    
    public function __construct(
        string $amuId = '',
        string $firstName = '',
        string $lastName = '',
        string $gender = '',
        string $userType = '',
        string $email = '',
        string $phone = '',
        string $dateOfBirth = '',
        string $city = '',
        ?int $year = null,
        ?string $parcours = null,
        ?string $td = null,
        ?string $tp = null,
        string $clearpassword = ''
    ) {
        $this->amuId = $amuId;
        $this->lastName = $lastName;
        $this->firstName = $firstName;
        $this->gender = $gender;
        $this->userType = $userType;
        $this->email = $email;
        $this->phone = $phone;
        $this->dateOfBirth = $dateOfBirth;
        $this->city = $city;
        $this->year = $year;
        $this->parcours = $parcours;
        $this->td = $td;
        $this->tp = $tp;
        $this->clearpassword = $clearpassword;
    }

    public static function createFromRegistrationData(array $data): self
    {
        $user = new self(
            $data['id'] ?? '',
            $data['lname'] ?? '',
            $data['fname'] ?? '',
            $data['gender'] ?? '',
            $data['user_type'] ?? '',
            $data['email'] ?? '',
            $data['tel'] ?? '',
            $data['dob'] ?? '',
            $data['city'] ?? '',
            $data['year'] ?? null,
            $data['parcours'] ?? null,
            $data['td'] ?? null,
            $data['tp'] ?? null
        );
        
        if (!empty($data['pwd'])) {
            $user->setPassword($data['pwd']);
        }
        
        return $user;
    }

    public function setPassword(string $password): void
    {

        $this->passwordHash = password_hash($password, PASSWORD_DEFAULT);
    }


    public function getClearPassword(): string
    {
        return $this->clearpassword;
    }

    public function setClearPassword(string $clearpassword): void
    {
        $this->clearpassword = $clearpassword;
    }

    public function save(): bool
    {
        $connection = database::getInstance();

        if ($this->userType === 'student') {
            $stmt = $connection->prepare("
            SELECT * FROM register_student(
                :email, 
                :lastName, 
                :firstName, 
                :passwordHash, 
                :phone, 
                :dateOfBirth,
                :city, 
                :amuId, 
                :parcours, 
                :year, 
                :td, 
                :tp
            )
        ");
            $stmt->execute([
                'email' => $this->email,
                'lastName' => $this->lastName,
                'firstName' => $this->firstName,
                'passwordHash' => $this->passwordHash,
                'phone' => $this->phone,
                'dateOfBirth' => $this->dateOfBirth,
                'city' => $this->city,
                'amuId' => $this->amuId,
                'parcours' => $this->parcours,
                'year' => $this->year,
                'td' => $this->td,
                'tp' => $this->tp
            ]);
        }
        elseif ($this->userType === 'professor') {
            $stmt = $connection->prepare("
            SELECT * FROM register_teacher(
                :email, 
                :lastName, 
                :firstName, 
                :passwordHash, 
                :phone, 
                :dateOfBirth,
                :city, 
                :amuId
            )
        ");
            $stmt->execute([
                'email' => $this->email,
                'lastName' => $this->lastName,
                'firstName' => $this->firstName,
                'passwordHash' => $this->passwordHash,
                'phone' => $this->phone,
                'dateOfBirth' => $this->dateOfBirth,
                'city' => $this->city,
                'amuId' => $this->amuId
            ]);
        }
        else {
            $stmt = $connection->prepare("
            SELECT * FROM register_user(
                :email, 
                :lastName, 
                :firstName, 
                :passwordHash, 
                :phone, 
                :dateOfBirth,
                :city
            )
        ");
            $stmt->execute([
                'email' => $this->email,
                'lastName' => $this->lastName,
                'firstName' => $this->firstName,
                'passwordHash' => $this->passwordHash,
                'phone' => $this->phone,
                'dateOfBirth' => $this->dateOfBirth,
                'city' => $this->city
            ]);
        }

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['success'] === true;
    }

    public function login(): bool
    {

        $connection = database::getInstance();
        $stmt = $connection->prepare("SELECT connection(?)");
        $stmt->execute([$this->email]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $composite = trim($row['connection'], '()');
        $parts = explode(',', $composite);

        $user_id = $parts[0];
        $passwordHash = $parts[1];
        $success = ($parts[2] === 't'); // PostgreSQL boolean: 't' = true, 'f' = false

        if($success == true){
            return password_verify($this->clearpassword, $passwordHash);
        }
        return false;
    }




    public static function existsByEmail(string $email): bool
    {
        try {
            $db = database::getInstance();
            $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
            $stmt->execute(['email' => $email]);
            return $stmt->fetchColumn() > 0;
        } catch (\PDOException $e) {
            error_log("Erreur vérification email: " . $e->getMessage());
            return false;
        }
    }

    public static function updatePasswordByEmail(string $email, string $newPassword): bool
    {
        try {
            $db = database::getInstance();
            $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE users SET password = :password_hash WHERE email = :email");
            return $stmt->execute([
                'password_hash' => $passwordHash,
                'email' => $email
            ]);
        } catch (\PDOException $e) {
            error_log("Erreur mise à jour mot de passe: " . $e->getMessage());
            return false;
        }
    }

    // Getters
    public function getId(): ?int { return $this->id; }
    public function getAmuId(): string { return $this->amuId; }
    public function getFirstName(): string { return $this->firstName; }
    public function getLastName(): string { return $this->lastName; }
    public function getFullName(): string { return $this->firstName . ' ' . $this->lastName; }
    public function getGender(): string { return $this->gender; }
    public function getUserType(): string { return $this->userType; }
    public function getEmail(): string { return $this->email; }
    public function getPhone(): string { return $this->phone; }
    public function getDateOfBirth(): string { return $this->dateOfBirth; }
    public function getCity(): string { return $this->city; }
    public function getYear(): ?string { return $this->year; }
    public function getParcours(): ?string { return $this->parcours; }
    public function getTd(): ?string { return $this->td; }
    public function getTp(): ?string { return $this->tp; }

    public function isStudent(): bool { return $this->userType === 'student'; }
    public function isProfessor(): bool { return $this->userType === 'professor'; }
    public function isCompany(): bool { return $this->userType === 'companies'; }
}