<?php
namespace Models\User;

use includes\database;

class User
{
    private array $fields = [
        'amuId' => '',
        'fname' => '',
        'lname' => '',
        'gender' => '',
        'user_type' => '',
        'email' => '',
        'tel' => '',
        'dateOfBirth' => '',
        'city' => '',
        'year' => null,
        'parcours' => null,
        'td' => null,
        'tp' => null
    ];

    private string $passwordHash = '';


    public function __construct(array $data = [])
    {
        foreach ($this->fields as $property => $default) {
            $this->fields[$property] = $data[$property] ?? $default;
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

    public function setPassword(string $password): void
    {
        $this->passwordHash = password_hash($password, PASSWORD_DEFAULT);
    }

    public function save(): bool
    {
        // TODO: Implémentation de la sauvegarde en base de données
        return true;
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
    public function getAmuId(): string { return $this->fields['amuId']; }
    public function getFFname(): string { return $this->fields['fname']; }
    public function getLLname(): string { return $this->fields['lname']; }
    public function getFullName(): string { return $this->fields['fname'] . ' ' . $this->fields['lname']; }
    public function getGender(): string { return $this->fields['gender']; }
    public function getUserType(): string { return $this->fields['user_type']; }
    public function getEmail(): string { return $this->fields['email']; }
    public function getPasswordHash(): string { return $this->passwordHash; }
    public function getPhone(): string { return $this->fields['tel']; }
    public function getDateOfBirth(): string { return $this->fields['dateOfBirth']; }
    public function getCity(): string { return $this->fields['city']; }
    public function getYear(): ?string { return $this->fields['year'] ?? null; }
    public function getParcours(): ?string { return $this->fields['parcours'] ?? null; }
    public function getTd(): ?string { return $this->fields['td'] ?? null; }
    public function getTp(): ?string { return $this->fields['tp'] ?? null; }

    public function isStudent(): bool { return $this->fields['user_type'] === 'student'; }
    public function isProfessor(): bool { return $this->fields['user_type'] === 'professor'; }
    public function isCompany(): bool { return $this->fields['user_type'] === 'companies'; }
}