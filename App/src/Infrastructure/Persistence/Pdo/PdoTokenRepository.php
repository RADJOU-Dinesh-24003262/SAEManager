<?php

namespace App\Infrastructure\Persistence\Pdo;

use App\Domain\Auth\Token;
use App\Domain\Auth\IRepository\ITokenRepository;
use Core\Database\Database;
use PDO;
use PDOException;
use DateTime;

/**
 * PDO implementation of the Token repository.
 * 
 * Handles persistence for password reset tokens.
 *
 * @package App\Infrastructure\Persistence\Pdo
 */
class PdoTokenRepository implements ITokenRepository
{
    private PDO $connection;

    public function __construct()
    {
        $this->connection = Database::getInstance();
    }

    /**
     * Saves a new password reset token.
     *
     * @param Token $token The token to save.
     * @return void
     * @throws PDOException If database operation fails.
     */
    public function save(Token $token): void
    {
        try {
            $stmt = $this->connection->prepare(
                "INSERT INTO password_resets (email, token, created_at, expires_at, used)
                 VALUES (LOWER(:email), :token, :created_at, :expires_at, :used)"
            );
            $stmt->execute([
                'email' => $token->getEmail(),
                'token' => $token->getToken(),
                'created_at' => $token->getCreatedAt()->format('Y-m-d H:i:s'),
                'expires_at' => $token->getExpiresAt()->format('Y-m-d H:i:s'),
                'used' => (int)$token->isUsed()
            ]);
        }
        catch (PDOException $e) {
            error_log("Error saving token: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Finds a token by its value.
     *
     * @param string $tokenValue The token string.
     * @return Token|null The token entity or null if not found.
     */
    public function findById(string $tokenValue): ?Token
    {
        try {
            $stmt = $this->connection->prepare(
                "SELECT email, token, created_at, expires_at, used 
                 FROM password_resets 
                 WHERE token = :token"
            );
            $stmt->execute(['token' => $tokenValue]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                return new Token(
                    $row['email'],
                    $row['token'],
                    new DateTime($row['created_at']),
                    new DateTime($row['expires_at']),
                    (bool)$row['used']
                    );
            }
            return null;
        }
        catch (PDOException $e) {
            error_log("Error finding token by value: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Marks a token as used.
     *
     * @param string $tokenValue The token string.
     * @return bool True on success, false on failure.
     */
    public function markAsUsed(string $tokenValue): bool
    {
        try {
            $stmt = $this->connection->prepare(
                "UPDATE password_resets SET used = TRUE WHERE token = :token"
            );
            return $stmt->execute(['token' => $tokenValue]);
        }
        catch (PDOException $e) {
            error_log("Error marking token as used: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Cleans up expired and used tokens.
     *
     * @return void
     */
    public function cleanupExpiredAndUsedTokens(): void
    {
        try {
            $stmt = $this->connection->prepare(
                "DELETE FROM password_resets WHERE expires_at < NOW() OR used = TRUE"
            );
            $stmt->execute();
        }
        catch (PDOException $e) {
            error_log("Error cleaning up tokens: " . $e->getMessage());
        }
    }

    /**
     * Deletes all tokens for a specific email.
     *
     * @param string $email The user's email.
     * @return void
     */
    public function deleteTokensByEmail(string $email): void
    {
        try {
            $stmt = $this->connection->prepare(
                "DELETE FROM password_resets WHERE email = LOWER(:email)"
            );
            $stmt->execute(['email' => $email]);
        }
        catch (PDOException $e) {
            error_log("Error deleting tokens by email: " . $e->getMessage());
        }
    }

    /**
     * Updates a token (not supported - tokens are immutable).
     *
     * @param Token $entity The token entity.
     * @return bool Always returns false.
     */
    public function update(Token $entity): bool
    {
        return false; // Can't update token, it's immutable
    }

    /**
     * Deletes a token by its value.
     *
     * @param string $tokenValue The token string.
     * @return bool True on success, false on failure.
     */
    public function delete(string $tokenValue): bool
    {
        try {
            $stmt = $this->connection->prepare(
                "DELETE FROM password_resets WHERE token = :token"
            );
            return $stmt->execute(['token' => $tokenValue]);
        }
        catch (PDOException $e) {
            error_log("Error deleting token: " . $e->getMessage());
            return false;
        }
    }

}