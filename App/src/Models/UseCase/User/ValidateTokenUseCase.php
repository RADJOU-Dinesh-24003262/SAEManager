<?php

namespace Models\UseCase\User;

use Models\UseCase\User\InterfaceDB\TokenRepositoryInterface;
use Services\TokenService;
use Core\Includes\Exception\ExceptionToken\ExceptionInvalidToken;

/**
 * Use Case for validating tokens (password reset or registration).
 *
 * Handles logic for checking token format, existence, usage and expiration.
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
     * The Token repository interface.
     *
     * @var TokenRepositoryInterface
     */
    private TokenRepositoryInterface $tokenRepository;

    /**
     * The Token service.
     *
     * @var TokenService
     */
    private TokenService $tokenService;


    /**
     * Constructor.
     *
     * @param TokenRepositoryInterface $tokenRepository The token repository.
     * @param TokenService             $tokenService    The Token Service.
     */
    public function __construct(TokenRepositoryInterface $tokenRepository, TokenService $tokenService)
    {
        $this->tokenRepository = $tokenRepository;
        $this->tokenService = $tokenService;
    }

    /**
     * Validates a token from the repository.
     *
     * @param string $token   The token string.
     * @param string $context Context for error messages (e.g., 'réinitialisation', 'confirmation').
     *
     * @return array<string, mixed> The token data from the database.
     *
     * @throws ExceptionInvalidToken If token invalid or expired.
     */
    public function execute(string $token, string $context = 'réinitialisation'): array
    {
        // 1. Check physical token format. Default to hex 64.
        if (!$this->tokenService->isValidFormat($token)) {
            throw new ExceptionInvalidToken("Ce lien de {$context} est invalide.");
        }

        // 2. Fetch token from database.
        $tokenData = $this->tokenRepository->findByToken($token);

        if (!$tokenData) {
            throw new ExceptionInvalidToken(
                "Ce lien de {$context} est invalide ou a expiré. Veuillez faire une nouvelle demande."
            );
        }

        // 3. Check if token was already used.
        if (isset($tokenData['used']) && $tokenData['used']) {
            throw new ExceptionInvalidToken("Ce lien de {$context} a déjà été utilisé.");
        }

        // 4. Check if token is expired.
        if (isset($tokenData['expires_at']) && $this->tokenService->isExpired($tokenData['expires_at'])) {
            throw new ExceptionInvalidToken("Ce lien de {$context} a expiré.");
        }

        return $tokenData;
    }
}
