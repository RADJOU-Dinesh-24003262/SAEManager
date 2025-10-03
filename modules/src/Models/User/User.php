<?php
namespace Models\User;

use includes\database;
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
    private ?string $year = null;
    private ?string $parcours = null;
    private ?string $td = null;
    private ?string $tp = null;

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
        ?string $year = null,
        ?string $parcours = null,
        ?string $td = null,
        ?string $tp = null
    ) {
        $this->amuId = $amuId;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
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
    }

    public static function createFromRegistrationData(array $data): self
    {
        $user = new self(
            $data['id'] ?? '',
            $data['fname'] ?? '',
            $data['lname'] ?? '',
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

    public function save(): bool
    {
        $connection = database::getInstance();


        if ($this->userType === 'student') {

            $str = "SELECT register_student(
            '$this->email',
            '$this->firstName',
            '$this->lastName',
            '$this->passwordHash',
            '$this->city',
            '$this->amuId',
            '$this->parcours',
            '$this->year',
            '$this->td',
            '$this->tp'
        );";
        }
        elseif ($this->getUserType() === 'professor') {
            $str = "SELECT register_teacher(
            '$this->email',
            '$this->firstName',
            '$this->lastName',
            '$this->passwordHash',
            '$this->city',
            '$this->amuId',
        );";
        }
        else {
            $str = "SELECT register_user(
            '$this->email',
            '$this->firstName',
            '$this->lastName',
            '$this->passwordHash',
            '$this->city',
            '$this->amuId',
        );";
        }
        $connection->query($str);
        return true;
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