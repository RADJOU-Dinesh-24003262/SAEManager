<?php

namespace Services;

use Exception;

/**
 * Service to handle file operations.
 *
 * This service manages the storage and deletion of SAE description files
 * in the server's filesystem.
 *
 * @category Services
 * @package  Src
 * @subpackage Services
 * @author     Dinesh Radjou <dinesh.radjou@univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class FileService
{
    /**
     * Storage directory for SAE descriptions.
     *
     * @var string
     */
    private const STORAGE_DIR = __DIR__ . '/../../../storage/sae_descriptions';

    /**
     * Validates that a file path is within the storage directory.
     *
     * @param string $filePath The full file path to validate.
     *
     * @return boolean True if the path is safe, false otherwise.
     */
    private static function isPathSafe(string $filePath): bool
    {
        $realPath = realpath($filePath);
        $storageDir = realpath(self::STORAGE_DIR);

        return $realPath && $storageDir && strpos($realPath, $storageDir) === 0;
    }

    /**
     * Saves content to a markdown file.
     *
     * @param string $content  The markdown content to save.
     * @param string $filename The desired base filename (without extension).
     *
     * @return string The generated filename with extension.
     * @throws Exception If validation fails or file operations fail.
     */
    public static function saveSaeDescription(string $content, string $filename): string
    {
        if (!is_dir(self::STORAGE_DIR)) {
            if (!mkdir(self::STORAGE_DIR, 0755, true)) {
                throw new Exception("Impossible de créer le dossier de stockage.");
            }
        }

        $safeFilename = preg_replace('/[^a-zA-Z0-9_-]/', '', $filename);
        if (empty($safeFilename)) {
            throw new Exception("Nom de fichier invalide.");
        }

        $fileNameWithExt = $safeFilename . '_' . uniqid() . '.md';
        $fullPath = realpath(self::STORAGE_DIR) . '/' . $fileNameWithExt;

        if (!self::isPathSafe($fullPath)) {
            throw new Exception("Accès refusé : chemin non autorisé.");
        }

        if (file_put_contents($fullPath, $content) === false) {
            throw new Exception("Impossible d'écrire le fichier de description.");
        }

        return $fileNameWithExt;
    }

    /**
     * Removes a file from storage.
     *
     * @param string $filename The name of the file to remove.
     *
     * @return boolean True if deleted successfully or didn't exist.
     * @throws Exception If the file path is unsafe or deletion fails.
     */
    public static function removeFile(string $filename): bool
    {
        if (empty($filename) || preg_match('#\.\.|/|\\\\#', $filename)) {
            throw new Exception("Nom de fichier invalide.");
        }

        $fullPath = realpath(self::STORAGE_DIR) . '/' . $filename;

        if (!self::isPathSafe($fullPath) || !is_file($fullPath)) {
            throw new Exception("Fichier non trouvé ou accès refusé.");
        }

        return unlink($fullPath);
    }

    /**
     * Retrieves the content of a file.
     *
     * @param string $filename The name of the file to retrieve.
     *
     * @return string The content of the file.
     * @throws Exception If the file is unsafe or not found.
     */
    public static function getSaeDescription(string $filename): string
    {
        if (empty($filename) || preg_match('#\.\.|/|\\\\#', $filename)) {
            throw new Exception("Nom de fichier invalide.");
        }

        $fullPath = realpath(self::STORAGE_DIR) . '/' . $filename;

        if (!self::isPathSafe($fullPath) || !is_file($fullPath)) {
            throw new Exception("Fichier non trouvé.");
        }

        return file_get_contents($fullPath) ?? '';
    }

    /**
     * Updates the content of an existing file.
     *
     * @param string $filename The name of the file to update.
     * @param string $content  The new content to write to the file.
     *
     * @return boolean True if updated successfully.
     * @throws Exception If validation fails or file operations fail.
     */
    public static function updateSaeDescription(string $filename, string $content): bool
    {
        if (empty($filename) || preg_match('#\.\.|/|\\\\#', $filename)) {
            throw new Exception("Nom de fichier invalide.");
        }

        $fullPath = realpath(self::STORAGE_DIR) . '/' . $filename;

        if (!self::isPathSafe($fullPath) || !is_file($fullPath)) {
            throw new Exception("Fichier non trouvé ou accès refusé.");
        }

        if (file_put_contents($fullPath, $content) === false) {
            throw new Exception("Impossible de mettre à jour le fichier de description.");
        }

        return true;
    }
}
