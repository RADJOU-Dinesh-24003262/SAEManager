<?php

namespace App\Infrastructure\Security;

/**
 * Input sanitizer for HTML escaping and XSS protection.
 * Provides methods to sanitize user input before processing or storage.
 *
 * @category Security
 * @package  App\Infrastructure\Security
 * @author   Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license  MIT License https://opensource.org/licenses/MIT
 * @link     https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class InputSanitizer
{
    /**
     * Sanitizes an array of data recursively.
     * Escapes HTML special characters to prevent XSS attacks.
     *
     * @param array<string, mixed> $data The data to sanitize.
     * @return array<string, mixed> The sanitized data.
     */
    public static function sanitize(array $data): array
    {
        array_walk_recursive($data, function (&$item) {
            if (is_string($item)) {
                $item = htmlspecialchars($item, ENT_QUOTES, 'UTF-8');
            }
        });

        return $data;
    }

    /**
     * Sanitizes a single string value.
     *
     * @param string $value The value to sanitize.
     * @return string The sanitized value.
     */
    public static function sanitizeString(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}