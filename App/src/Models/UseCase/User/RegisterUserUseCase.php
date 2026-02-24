<?php

namespace Models\UseCase\User;

use Core\includes\exception\ExceptionEmailAlreadyExists;
use InvalidArgumentException;
use Models\Entity\User\Client;
use Models\Entity\User\Professor;
use Models\Entity\User\Student;
use Models\Entity\User\User;
use Models\Entity\User\UserFactory;
use Models\UseCase\User\InterfaceDB\UserInterface;
use Models\UseCase\User\InterfaceDB\StudentInterface;
use Models\UseCase\User\InterfaceDB\ProfessorInterface;
use Models\UseCase\User\InterfaceDB\ClientInterface;

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
     * The Student repository interface.
     * @var StudentInterface
     */
    private StudentInterface $studentInterface;

    /**
     * The Professor repository interface.
     * @var ProfessorInterface
     */
    private ProfessorInterface $professorInterface;

    /**
     * The Client repository interface.
     * @var ClientInterface
     */
    private ClientInterface $clientInterface;

    /**
     * The User repository interface (for common checks like email existence).
     * @var UserInterface
     */
    private UserInterface $userInterface;

    /**
     * The PDO interface.
     * @var UserInterface
     */
    private UserInterface $pdoInterface;

    /**
     * Constructor.
     *
     * @param StudentInterface   $studentInterface   The Student repository.
     * @param ProfessorInterface $professorInterface The Professor repository.
     * @param ClientInterface    $clientInterface    The Client repository.
     * @param UserInterface      $userInterface      The User repository.
     */
    public function __construct(
        StudentInterface $studentInterface,
        ProfessorInterface $professorInterface,
        ClientInterface $clientInterface,
        UserInterface $userInterface
    ) {
        $this->studentInterface = $studentInterface;
        $this->professorInterface = $professorInterface;
        $this->clientInterface = $clientInterface;
        $this->userInterface = $userInterface;
    }

    /**
     * Registers a new user.
     *
     * @param array<string, mixed> $data The validated user data.
     *
     * @return User|null The registered user.
     *
     * @throws ExceptionEmailAlreadyExists If the email is already in the database.
     * @throws \Exception If the user creation fails.
     */
    public function execute(array $data): ?User
    {
        // 1. Create the user entity
        $user = UserFactory::create($data);

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

        // 5. Save the user using the specific repository
        $result = false;
        if ($user instanceof Student) {
            $this->pdoInterface = $this->studentInterface;
        } elseif ($user instanceof Professor) {
            $this->pdoInterface = $this->professorInterface;
        } elseif ($user instanceof Client) {
            $this->pdoInterface = $this->clientInterface;
        }

        $result = $this->pdoInterface->insert($user);

        if ($result === false) {
            throw new \Exception("Failed to create user");
        }

        if (is_int($result)) {
            $user->setUserId($result);
        }

        return $this->pdoInterface->findById($user->getUserId());
    }
}
