<?php

namespace Models\UseCase\User;

use Core\Includes\Exception\ExceptionEmailAlreadyExists;
use Core\Includes\Exception\ExceptionToken\ExceptionInvalidToken;
use Models\Entity\User\User;
use Models\Entity\User\UserFactory;
use Models\UseCase\User\InterfaceDB\ClientInterface;
use Models\UseCase\User\InterfaceDB\PendingRegistrationInterface;
use Models\UseCase\User\InterfaceDB\ProfessorInterface;
use Models\UseCase\User\InterfaceDB\StudentInterface;
use Models\UseCase\User\InterfaceDB\UserInterface;

/**
 * Use Case for handling two-factor authentication (email confirmation).
 *
 * Validates the token, creates the user in the correct table,
 * marks the pending registration as used and purges expired entries.
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
     * The PendingRegistration repository interface.
     *
     * @var PendingRegistrationInterface
     */
    private PendingRegistrationInterface $pendingRegistrationsInterface;

    /**
     * The Validate Token use case.
     *
     * @var ValidateTokenUseCase
     */
    private ValidateTokenUseCase $validateTokenUseCase;

    /**
     * Constructor.
     *
     * @param StudentInterface             $studentInterface             The Student repository.
     * @param ProfessorInterface           $professorInterface           The Professor repository.
     * @param ClientInterface              $clientInterface              The Client repository.
     * @param PendingRegistrationInterface $pendingRegistrationInterface The PendingRegistration repository.
     * @param ValidateTokenUseCase         $validateTokenUseCase         The ValidateToken use case.
     */
    public function __construct(
        StudentInterface $studentInterface,
        ProfessorInterface $professorInterface,
        ClientInterface $clientInterface,
        PendingRegistrationInterface $pendingRegistrationInterface,
        ValidateTokenUseCase $validateTokenUseCase
    ) {
        $this->studentInterface = $studentInterface;
        $this->professorInterface = $professorInterface;
        $this->clientInterface = $clientInterface;
        $this->pendingRegistrationsInterface = $pendingRegistrationInterface;
        $this->validateTokenUseCase = $validateTokenUseCase;
    }

    /**
     * Validates the token, creates the user and cleans up the pending registration.
     *
     * @param string $token The email confirmation token.
     *
     * @return User The newly created user.
     *
     * @throws \Exception If the token is invalid or user creation fails.
     */
    public function execute(string $token): User
    {
        // 1. Fetch and validate pending registration.
        $data = $this->validateTokenUseCase->execute($token, 'confirmation');

        // 3. Build the User entity.
        $user = UserFactory::create($data);

        // 4. Select the correct repository based on user type.
        $repositories = [
            'student' => $this->studentInterface,
            'professor' => $this->professorInterface,
            'client' => $this->clientInterface,
        ];

        if (!isset($repositories[$user->getUserType()])) {
            throw new \Exception("Type d'utilisateur inconnu : " . $user->getUserType());
        }

        $pdoInterface = $repositories[$user->getUserType()];

        // 5. Insert the user in the appropriate table.
        $result = $pdoInterface->insert($user);

        if ($result === false) {
            throw new \Exception("Échec de la création de l'utilisateur.");
        }

        if (is_int($result)) {
            $user->setUserId($result);
        }

        // 6. Mark token as used then purge expired/used rows.
        $this->pendingRegistrationsInterface->markAsUsed($token);
        $this->pendingRegistrationsInterface->purgeExpired();

        // 7. Return the fully hydrated user from DB.
        $user = $pdoInterface->findById($user->getUserId());

        if (!$user instanceof User) {
            throw new \Exception("Utilisateur non trouvé après création.");
        }

        return $user;
    }
}
