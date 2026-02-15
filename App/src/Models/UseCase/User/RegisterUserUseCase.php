<?php

namespace Models\UseCase\User;

use Core\includes\exception\ExceptionEmailAlreadyExists;
use InvalidArgumentException;
use Models\Entity\User\Client;
use Models\Entity\User\Professor;
use Models\Entity\User\Student;
use Models\Entity\User\User;
use Models\UseCase\User\InterfaceDB\UserInterface;

/**
 * Use Case for user registration.
 *
 * Handles the registration logic.
 *
 * @category   Models
 * @package    Src
 * @subpackage Models/UseCase/User
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class RegisterUserUseCase
{
    /**
     * The User repository interface.
     *
     * @var UserInterface<User>
     */
    private UserInterface $userInterface;

    /**
     * Constructor.
     *
     * @param UserInterface<User> $userInterface The User repository.
     */
    public function __construct(UserInterface $userInterface)
    {
        $this->userInterface = $userInterface;
    }

    /**
     * Registers a new user.
     *
     * @param array<string, mixed> $data The validated user data.
     *
     * @return User The registered user.
     *
     * @throws ExceptionEmailAlreadyExists If the email is already in the database.
     * @throws InvalidArgumentException If the user type is invalid.
     */
    public function execute(array $data): User
    {
        // 1. Create the user entity
        $user = $this->createUserEntity($data);

        // 2. Set password (hash it)
        if (isset($data['password'])) {
            $user->setPassword($data['password']);
        }

        // 3. Add domain name to email if needed
        $user->addDomainNameToEmail();

        // 4. Check if email already exists
        if ($this->userInterface->existsByEmail($user->getEmail())) {
            throw new ExceptionEmailAlreadyExists($user->getEmail());
        }

        // 5. Save the user
        $result = $this->userInterface->create($user);
        if ($result === false) {
            throw new \Exception("Failed to create user");
        }

        return $result;
    }

    /**
     * Creates the appropriate User entity based on type.
     *
     * @param array<string, mixed> $data The user data.
     * @return User The user entity.
     * @throws InvalidArgumentException If user type is invalid.
     */
    private function createUserEntity(array $data): User
    {
        $userType = $data['user_type'] ?? '';

        return match ($userType) {
                'student' => new Student($data),
                'professor' => new Professor($data),
                'client' => new Client($data),
                default => throw new InvalidArgumentException("Type d'utilisateur invalide : {$userType}"),
        };
    }
}
