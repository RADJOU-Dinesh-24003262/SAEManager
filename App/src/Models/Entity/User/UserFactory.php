<?php

namespace Models\Entity\User;

use RuntimeException;

/**
 * Factory for creating User entities according to their type.
 *
 * Implements the Liskov Substitution Principle (LSP) handling
 * user creation centrally in the Domain without runtime exceptions.
 *
 * @category   Models
 * @package    Src
 * @subpackage Models/Entity/User
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class UserFactory
{
    /**
     * Map of user type codes to string representations.
     */
    private const TYPE_MAP = [
        '0' => 'student',
        '1' => 'professor',
        '2' => 'client'
    ];

    /**
     * Creates a User entity from an array of data.
     *
     * @param array<string, mixed> $data The user data.
     * @return User The instantiated User.
     */
    public static function create(array $data): User
    {
        $type = $data['user_type'] ?? 'student';

        if (array_key_exists((string)$type, self::TYPE_MAP)) {
            $type = self::TYPE_MAP[(string)$type];
        }

        return match ($type) {
                'professor' => new Professor($data),
                'client' => new Client($data),
                default => new Student($data),
        };
    }
}
