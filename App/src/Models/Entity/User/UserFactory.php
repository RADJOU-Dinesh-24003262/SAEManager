<?php

namespace Models\Entity\User;

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
     * Map of user type codes or strings to their respective User entity classes.
     * @var array<int|string, class-string<User>>
     */
    private static array $typeRegistry = [
        'student' => Student::class ,
        'professor' => Professor::class ,
        'client' => Client::class ,
        '0' => Student::class ,
        '1' => Professor::class ,
        '2' => Client::class ,
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

        $className = self::$typeRegistry[$type];

        return new $className($data);
    }
}
