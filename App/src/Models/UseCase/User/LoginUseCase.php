<?php

namespace Models\UseCase\User;

use Core\includes\exception\ExceptionValidation\ExceptionValidationLogin;
use Models\Entity\User\Student;
use Models\Entity\User\User;
use Models\UseCase\User\InterfaceDB\UserInterface;

/**
 * Use Case for user login.
 *
 * Handles the authentication logic.
 *
 * @category   Models
 * @package    Src
 * @subpackage Models/UseCase/User
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class LoginUseCase
{
    /**
     * The User repository interface.
     *
     * @var UserInterface
     */
    private UserInterface $userInterface;

    /**
     * Constructor.
     *
     * @param UserInterface $userInterface The User repository.
     */
    public function __construct(UserInterface $userInterface)
    {
        $this->userInterface = $userInterface;
    }

    /**
     * Authenticates a user.
     *
     * @param string $email    The user's email.
     * @param string $password The user's plain text password.
     *
     * @return User The authenticated user.
     *
     * @throws ExceptionValidationLogin If authentication fails.
     */
    public function execute(string $email, string $password): User
    {
        $user = $this->userInterface->findByEmail($email);

        if (!$user || !$user->verifyPassword($password)) {
            throw new ExceptionValidationLogin();
        }



        return $user;
    }
}
