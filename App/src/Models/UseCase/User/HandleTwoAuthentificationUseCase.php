<?php

namespace Models\UseCase\User;

use Core\Includes\Exception\ExceptionToken\ExceptionInvalidToken;
use Core\Includes\Exception\ExceptionEmailAlreadyExists;
use Models\Entity\User\Client;
use Models\Entity\User\Professor;
use Models\Entity\User\Student;
use Models\Entity\User\User;
use Models\Entity\User\UserFactory;
use Models\UseCase\User\InterfaceDB\ClientInterface;
use Models\UseCase\User\InterfaceDB\PendingRegistrationInterface;
use Models\UseCase\User\InterfaceDB\ProfessorInterface;
use Models\UseCase\User\InterfaceDB\StudentInterface;
use Models\UseCase\User\InterfaceDB\UserInterface;

/**
 * Use Case for user registration.
 *
 * Stores the user data in pending_registrations with a verification token.
 * The account is only created in users once the confirmation link is clicked.
 *
 * @category   Models
 * @package    Src
 * @subpackage Models/UseCase/User
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class HandleTwoAuthentificationUseCase
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

    private PendingRegistrationInterface $pendingRegistrationsInterface;


        /**
     * The PDO interface.
     * @var StudentInterface|ProfessorInterface|ClientInterface
     */
    private StudentInterface|ProfessorInterface|ClientInterface $pdoInterface;

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
        UserInterface $userInterface,
        PendingRegistrationInterface $pendingRegistrationInterface
    ) {
        $this->studentInterface = $studentInterface;
        $this->professorInterface = $professorInterface;
        $this->clientInterface = $clientInterface;
        $this->userInterface = $userInterface;
        $this->pendingRegistrationsInterface = $pendingRegistrationInterface;
    }
    /**
     * Validates data and stores it in pending_registrations.
     *
     * @param array<string, mixed> $data The validated user data.
     *
     * @return string The verification token to include in the confirmation email.
     *
     * @throws ExceptionEmailAlreadyExists If the email already exists in users or pending_registrations.
     * @throws Exception If the insert fails.
     */
    public function execute(string $token): User
    {

        $this->pendingRegistrationsInterface->purgeExpired();

        $data = $this->pendingRegistrationsInterface->findValidByToken($token);

        if (!$data) {
            throw new ExceptionInvalidToken('Token invalide ou expiré.');
        }

        $user = UserFactory::create($data);

        $repositories = [
            'student' => $this->studentInterface,
            'professor' => $this->professorInterface,
            'client' => $this->clientInterface,
        ];

        $this->pdoInterface = $repositories[$user->getUserType()];

        $result = $this->pdoInterface->insert($user);

        if ($result === false) {
            throw new \Exception("Failed to create user");
        }

        if (is_int($result)) {
            $user->setUserId($result);
        }

        $this->pendingRegistrationsInterface->markAsUsed($token);

        return $this->pdoInterface->findById($user->getUserId());
    }
}
