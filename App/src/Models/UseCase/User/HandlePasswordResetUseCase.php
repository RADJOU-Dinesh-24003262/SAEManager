<?php

namespace Models\UseCase\User;

use Core\includes\exception\ExceptionPasswordUpdateFailed;
use Core\includes\exception\ExceptionToken\ExceptionInvalidToken;
use Core\includes\exception\ExceptionValidation\ExceptionValidationResetPassword;
use Models\Repository\User\PdoUserRepository;
use Models\UseCase\User\ResetPasswordUseCase;
use Services\TokenService;

/**
 * Use case to handle password reset.
 *
 * @category   Models
 * @package    Src
 * @subpackage Models/UseCase/User
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class HandlePasswordResetUseCase
{
    /**
     * The user repository.
     *
     * @var PdoUserRepository
     */
    private PdoUserRepository $userRepository;

    /**
     * Constructor.
     *
     * @param PdoUserRepository $userRepository Repo for users.
     */
    public function __construct(PdoUserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Execute the password reset process.
     *
     * @param string $token    The reset token.
     * @param string $password The new password.
     * @return string The email associated with the token.
     * @throws ExceptionInvalidToken If token is invalid.
     * @throws ExceptionValidationResetPassword If validation fails.
     * @throws ExceptionPasswordUpdateFailed If password update fails.
     */
    public function execute(string $token, string $password): string
    {
        // Validate token.
        $tokenData = TokenService::validateToken($token);
        $email = $tokenData['email'];

        // Reset password using existing UseCase (reusing existing logic adhering to DRY).
        $resetPasswordUseCase = new ResetPasswordUseCase($this->userRepository);
        $resetPasswordUseCase->execute($email, $password);

        // Mark token as used.
        TokenService::markTokenAsUsed($token);

        return $email;
    }
}
