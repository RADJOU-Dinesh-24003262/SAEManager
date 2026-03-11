<?php

namespace Services;

/**
 * Service to handle generic token operations (generation, formatting, expiration).
 *
 * @category   Services
 * @package    Src
 * @subpackage Services
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class TokenService
{
    /**
     * Generate a cryptographically secure token.
     *
     * @return string The generated token.
     */
    public static function generate(): string
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * Validate the physical format of a token.
     *
     * @param string $token The token string.
     * @return boolean True if format is valid.
     */
    public static function isValidFormat(string $token): bool
    {
        return (bool)preg_match('/^[a-f0-9]{64}$/i', $token);
    }

    /**
     * Check if a given expiration datetime string is expired.
     *
     * @param string $expiresAt The expiration datetime string.
     * @return boolean True if expired.
     */
    public static function isExpired(string $expiresAt): bool
    {
        return strtotime($expiresAt) < time();
    }
    
}
