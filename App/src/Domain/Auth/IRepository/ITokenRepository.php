<?php

namespace App\Domain\Auth\IRepository;

use App\Domain\Auth\Token;
use Core\Models\Repository\RepositoryInterface;

/**
 * Repository interface for Token persistence.
 * 
 * Extends RepositoryInterface with Token-specific operations.
 *
 * @package App\Domain\Auth\IRepository
 * @extends RepositoryInterface<Token>
 */
interface ITokenRepository extends RepositoryInterface
{
    /**
     * Saves a new token.
     *
     * @param Token $token The token to save.
     * @return void
     * @throws \PDOException If database operation fails.
     */
    public function save(Token $token): void;

    /**
     * Finds a token by its string value.
     *
     * @param string $token The token string.
     * @return Token|null The token if found, null otherwise.
     * @throws \PDOException If database operation fails.
     */
    public function findById(string $token): ?Token;

    /**
     * Marks a token as used.
     *
     * @param string $token The token string.
     * @return bool True on success, false on failure.
     * @throws \PDOException If database operation fails.
     */
    public function markAsUsed(string $token): bool;

    /**
     * Cleans up expired and used tokens from the database.
     *
     * @return void
     * @throws \PDOException If database operation fails.
     */
    public function cleanupExpiredAndUsedTokens(): void;

    /**
     * Deletes all tokens associated with an email address.
     *
     * @param string $email The email address.
     * @return void
     * @throws \PDOException If database operation fails.
     */
    public function deleteTokensByEmail(string $email): void;
}