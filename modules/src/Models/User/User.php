<?php

namespace Models\User;

use includes\database;
use includes\exception\ExceptionFetchDataBD;
use includes\exception\ExceptionPasswordUpdateFailed;
use PDO;
use PDOException;
use includes\exception\ExceptionValidationLogin;

class User
{
    private string $amuId = '';
    private string $firstName = '';
    private string $lastName = '';
    private string $userType = '';
    private string $email = '';
    private string $passwordHash = '';
    private string $phone = '';
    private string $dateOfBirth = '';
    private string $city = '';
    private ?int $year = null;
    private ?string $parcours = null;
    private ?string $td = null;
    private ?string $tp = null;

    private function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            if ($key === 'password') {
                continue; // Skip password, use setPassword method instead
            }
            $this->$key = $value;
        }
    }

    public static function createFromRegistrationData(array $data): self
    {
        $user = new self($data);
        $user->setPassword($data['password']);
        return $user;
    }

    public static function createFromLoginData(array $data): self
    {
        $user = new self($data);
        $user->login($user->email, $data['password']);
        $user->fetchDataFromDatabase($data['email']);
        return $user;
    }

    public function setPassword(string $password): void
    {

        $this->passwordHash = password_hash($password, PASSWORD_DEFAULT);
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
        } elseif ($this->userType === 'professor') {
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
        } else {
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

    public function login(string $email, string $password): void
    {

        $connection = database::getInstance();
        $stmt = $connection->prepare("SELECT connection(?)");
        $stmt->execute([$email]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $composite = trim($row['connection'], '()');
        $parts = explode(',', $composite);

        $user_id = $parts[0];
        $passwordHash = $parts[1];
        $success = ($parts[2] === 't'); // PostgreSQL boolean: 't' = true, 'f' = false

        if (!($success === true && password_verify($password, $passwordHash))) {
            throw new ExceptionValidationLogin();
        }
    }

    public function fetchDataFromDatabase(string $email): void
    {
        try {
            $db = database::getInstance();
            $stmt = $db->prepare("SELECT * FROM users WHERE email = :email");
            $stmt->execute(['email' => $email]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($data) {
                $this->amuId = $data['amuid'] ?? $data['amuid2'] ?? '';
                $this->firstName = $data['first_name'];
                $this->lastName = $data['last_name'];
                $this->userType = $data['amuid'] ? 'student' : ($data['amuid2'] ? 'professor' : 'companies');
                $this->email = $data['email'];
                $this->passwordHash = $data['password'];
                $this->phone = $data['phone'];
                $this->dateOfBirth = $data['dateofbirth'];
                $this->city = $data['city'];
                if ($this->isStudent()) {
                    $this->year = (int)$data['year'];
                    $this->parcours = $this->year !== 1 ? $data['parcours'] : null;
                    $this->td = $data['td'];
                    $this->tp = $data['tp'];
                }
            } else {
                throw new ExceptionFetchDataBD();
            }
        } catch (PDOException $e) {
            error_log("Erreur récupération données utilisateur: " . $e->getMessage());
            throw new ExceptionFetchDataBD();
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

    public static function updatePasswordByEmail(string $email, string $newPassword): void
    {
        try {
            $db = database::getInstance();
            $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE users SET password = :password_hash WHERE email = :email");
            if ($stmt->execute(['password_hash' => $passwordHash, 'email' => $email]) && $stmt->rowCount() === 0) {
                throw new ExceptionPasswordUpdateFailed("Aucun utilisateur trouvé avec cet email.");
            }
        } catch (\PDOException $e) {
            error_log("Erreur mise à jour mot de passe: " . $e->getMessage());
            throw new ExceptionPasswordUpdateFailed("Erreur lors de la mise à jour du mot de passe.");
        }
    }

    // Getters
    public function getAmuId(): string
    {
        return $this->amuId;
    }
    public function getFirstName(): string
    {
        return $this->firstName;
    }
    public function getLastName(): string
    {
        return $this->lastName;
    }
    public function getFullName(): string
    {
        return $this->firstName . ' ' . $this->lastName;
    }
    public function getUserType(): string
    {
        return $this->userType;
    }
    public function getEmail(): string
    {
        return $this->email;
    }
    public function getPasswordHash(): string
    {
        return $this->passwordHash;
    }
    public function getPhone(): string
    {
        return $this->phone;
    }
    public function getDateOfBirth(): string
    {
        return $this->dateOfBirth;
    }
    public function getCity(): string
    {
        return $this->city;
    }
    public function getYear(): ?int
    {
        return $this->year;
    }
    public function getParcours(): ?string
    {
        return $this->parcours;
    }
    public function getTd(): ?string
    {
        return $this->td;
    }
    public function getTp(): ?string
    {
        return $this->tp;
    }

    public function isStudent(): bool
    {
        return $this->userType === 'student';
    }
    public function isProfessor(): bool
    {
        return $this->userType === 'professor';
    }
    public function isCompany(): bool
    {
        return $this->userType === 'companies';
    }
}
