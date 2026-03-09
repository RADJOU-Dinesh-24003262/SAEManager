<?php

namespace Models\UseCase\User;

use DateTimeImmutable;
use Exception;
use Core\Includes\Exception\ExceptionEmailAlreadyExists;
use InvalidArgumentException;
use Models\Entity\User\Client;
use Models\Entity\User\Professor;
use Models\Entity\User\Student;
use Models\Entity\User\UserFactory;
use Models\UseCase\User\InterfaceDB\PendingRegistrationInterface;
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
class RegisterUserUseCase
{
    /**
     * Token validity duration in seconds (10 minutes).
     */
    private const TOKEN_TTL = 600;

    /**
     * The User repository interface (email existence check).
     * @var UserInterface
     */
    private UserInterface $userInterface;

    /**
     * The PendingRegistration repository interface.
     * @var PendingRegistrationInterface
     */
    private PendingRegistrationInterface $pendingInterface;

    /**
     * Constructor.
     *
     * @param UserInterface                $userInterface    The User repository.
     * @param PendingRegistrationInterface $pendingInterface The PendingRegistration repository.
     */
    public function __construct(
        UserInterface                $userInterface,
        PendingRegistrationInterface $pendingInterface
    ) {
        $this->userInterface    = $userInterface;
        $this->pendingInterface = $pendingInterface;
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
    public function execute(array $data): string
    {
        // 1. Build entity — applies domain name logic, typing, etc.
        $user = UserFactory::create($data);

        if (isset($data['password'])) {
            $user->setPassword($data['password']);
        }

        // 2. Check email not already confirmed in users
        if ($this->userInterface->existsByEmail($user->getEmail())) {
            throw new ExceptionEmailAlreadyExists($user->getEmail());
        }

        // 3. Check email not already pending
        if ($this->pendingInterface->existsByEmail($user->getEmail())) {
            throw new ExceptionEmailAlreadyExists($user->getEmail());
        }

        // 4. Generate token and expiry
        $token     = bin2hex(random_bytes(32));
        $expiresAt = new DateTimeImmutable('+' . self::TOKEN_TTL . ' seconds');

        // 5. Extract role-specific fields
        $amuId = null;
        $td    = null;
        $tp    = null;
        $major = null;
        $year  = null;

        if ($user instanceof Student) {
            $amuId = $user->getAmuId();
            $td    = $user->getTd();
            $tp    = $user->getTp();
            $major = $user->getMajor();
            $year  = $user->getYear();
        } elseif ($user instanceof Professor) {
            $amuId = $user->getAmuId();
        }
        // Client has no extra fields

        // 6. Insert into pending_registrations
        $result = $this->pendingInterface->insert(
            token:     $token,
            firstName: $user->getFirstName(),
            lastName:  $user->getLastName(),
            email:     $user->getEmail(),
            phone:     $user->getPhone(),
            password:  $user->getPasswordHash(),
            status:    $user->getUserType(),
            expiresAt: $expiresAt,
            amuId:     $amuId,
            td:        $td,
            tp:        $tp,
            major:     $major,
            year:      $year
        );

        if ($result === false) {
            throw new Exception('Failed to store pending registration.');
        }

        // 7. Return token for RegistrationMailer
        return $token;
    }
}