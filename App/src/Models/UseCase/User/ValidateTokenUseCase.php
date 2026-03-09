<?php

namespace Models\UseCase\User;

use Core\Includes\Exception\ExceptionPasswordUpdateFailed;
use Models\UseCase\User\InterfaceDB\PasswordResetInterface;
use Models\UseCase\User\InterfaceDB\UserInterface;
use Models\Entity\User\User;

/**
 * Use Case for resetting user password (forgot password flow).
 *
 * Handles logic for resetting password via email/token flow.
 *
 * @category   Models
 * @package    Src
 * @subpackage Models/UseCase/User
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class ValidateTokenUseCase
{
    /**
     * The User repository interface.
     *
     * @var UserInterface
     */

    private PasswordResetInterface $passwordResetInterface;

    /**
     * Constructor.
     *
     * @param UserInterface $userInterface The User repository.
     */
    public function __construct(PasswordResetInterface $passwordResetInterface)
    {
        $this->passwordResetInterface = $passwordResetInterface;
    }

    /**
     * Resets the user's password using email.
     *
     * @param string $token
     *
     * @return void
     *
     * @throws ExceptionPasswordUpdateFailed If user not found or update fails.
     */
    public function execute(string $token): array
    {
        // Validate token.
        $tokenData = $this->passwordResetInterface->validateToken($token);

        return $tokenData;
    }
}
