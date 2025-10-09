<?php
namespace Models\User;

use includes\database;
use PDO;
use PDOException;

class User
{
    private string $amuId = '';
    private string $firstName = '';
    private string $lastName = '';
    private string $gender = '';
    private string $userType = '';
    private string $email = '';
    private string $passwordHash = '';
    private string $phone = '';
    private string $dateOfBirth = '';
    private string $city = '';
    private ?int $year = null;
    private ?string $parcours = null;
    private ?int $td = null;
    private ?int $tp = null;
    
    private function __construct(array $data = []) {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }

    public static function createFromRegistrationData(array $data): self
    {
        $user = new self($data);
        
        if (!empty($data['pwd'])) {
            $user->setPassword($data['pwd']);
        }
        
        return $user;
    }

    public static function createFromLoginData(array $data): self
    {
        $user = new self($data);
        var_dump($data);
        $user->login($user->email, $user->password);
        $user->fetchDataFromDatabase($email);
        return $user;
    }

    public function setPassword(string $password): void
    {

        $this->passwordHash = password_hash($password, PASSWORD_DEFAULT);

        //echo($this->email. $this->passwordHash);
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
                'city' => $this->city,
                'amuId' => $this->amuId
            ]);
        }

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['success'] === true;
    }

    public function login(string $email, string $password): void
    {

        $connection = database::getInstance();
        $stmt = $connection->prepare("SELECT connection(?)");
        $stmt->execute([$email]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        var_dump($row);
        // Parser le type composite: '(email,hash,t)'
        $composite = trim($row['connection'], '()');
        $parts = explode(',', $composite);

        $user_id = $parts[0];
        $passwordHash = $parts[1];
        $success = ($parts[2] === 't'); // PostgreSQL boolean: 't' = true, 'f' = false

        var_dump($success, $user_id, $passwordHash);

        if( !($success === true && password_verify($password, $passwordHash)) ) {
            throw ExeptionValidationLogin(); 
        }
    }

    public function fetchDataFromDatabase(string $email): bool
    {
        try {
            $db = database::getInstance();
            $stmt = $db->prepare("SELECT * FROM users WHERE email = :email");
            $stmt->execute(['email' => $email]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($data) {
                $this->amuId = $data['amu_id'];
                $this->firstName = $data['first_name'];
                $this->lastName = $data['last_name'];
                $this->gender = $data['gender'];
                $this->userType = $data['user_type'];
                $this->email = $data['email'];
                $this->passwordHash = $data['password'];
                $this->phone = $data['phone'];
                $this->dateOfBirth = $data['date_of_birth'];
                $this->city = $data['city'];
                if (isuserType() === 'student') {
                    $this->year = (int)$data['year'];
                    $this->parcours = $this->year !== 1 ? $data['parcours'] : null;
                    $this->td = (int)$data['td'];
                    $this->tp = (int)$data['tp'];
                }
            }else {
                return false; // No user found
            }
        } catch (PDOException $e) {
            error_log("Erreur récupération données utilisateur: " . $e->getMessage());
            return false;
        }
    }

    public static function existsByEmail(string $email): bool
    {
        try {
            $db = database::getInstance();
            $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
            $stmt->execute(['email' => $email]);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
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
    public function getYear(): ?int { return $this->year; }
    public function getParcours(): ?string { return $this->parcours; }
    public function getTd(): ?int { return $this->td; }
    public function getTp(): ?int { return $this->tp; }

    public function isStudent(): bool { return $this->userType === 'student'; }
    public function isProfessor(): bool { return $this->userType === 'professor'; }
    public function isCompany(): bool { return $this->userType === 'companies'; }
}