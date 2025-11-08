<?php

namespace Validator\Registration;

/**
 * Factory for creating registration validators
 * Provides the appropriate validator based on user type
 *
 * @category Validator
 * @package  Src
 * @author Alexandre Benhafessa <alexandre.benhafessa@etu.univ-amu.fr>
 * @author François Dargentolle <francois.dargentolle@etu.univ-amu.fr>
 * @author William Edelstein <william.edelstein@etu.univ-amu.fr>
 * @author Nathan Griguer <nathan.griguer@etu.univ-amu.fr>
 * @author Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license  MIT License https://opensource.org/licenses/MIT
 * @link     https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class RegistrationValidatorFactory
{
    /**
     * Creates and returns the appropriate validator for the given user type
     *
     * @param string $userType The type of user (student, professor, client).
     * @return AbstractRegistrationValidator The appropriate validator instance
     * @throws \InvalidArgumentException If the user type is invalid.
     */
    public static function create(string $userType): AbstractRegistrationValidator
    {
        return match ($userType) {
            'student' => new StudentRegistrationValidator(),
            'professor' => new ProfessorRegistrationValidator(),
            'client' => new ClientRegistrationValidator(),
            default => throw new \InvalidArgumentException(
                "Type d'utilisateur invalide : {$userType}. " .
                "Types acceptés : student, professor, client"
            ),
        };
    }

    /**
     * Gets a list of all available user types
     *
     * @return array<string> List of valid user types
     */
    public static function getAvailableUserTypes(): array
    {
        return ['student', 'professor', 'client'];
    }

    /**
     * Checks if a user type is valid
     *
     * @param string $userType The user type to check.
     * @return boolean True if valid, false otherwise
     */
    public static function isValidUserType(string $userType): bool
    {
        return in_array($userType, self::getAvailableUserTypes(), true);
    }
}
