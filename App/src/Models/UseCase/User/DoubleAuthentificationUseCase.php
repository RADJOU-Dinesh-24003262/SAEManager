<?php

namespace Models\UseCase\User;

use App\Services\Auth\RegistrationMailer;
use Core\includes\exception\ExceptionEmailAlreadyExists;
use Core\includes\exception\ExceptionSpam;
use Core\includes\exception\ExceptionToken\ExceptionCreationTokenFailed;
use Services\TokenService;


/**
 * Use Case for processing forgot password requests.
 *
 * @category   UseCase
 * @package    Models\UseCase\User
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class DoubleAuthentificationUseCase
{
    /**
     * Execute the process.
     *
     * @param string $email The email address.
     * @return void
     * @throws ExceptionCreationTokenFailed If token generation fails.
     * @throws ExceptionEmailAlreadyExists If email exists.
     * @throws ExceptionSpam If spam detected.
     */
    public function execute(string $email): void
    {
            $token = TokenService::createToken($email);
            RegistrationMailer::send($email, $token);
    }
}
