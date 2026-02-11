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
     * Saves content to a markdown file.
     *
     * Creates the storage directory if it does not exist, sanitizes the filename,
     * and saves the content.
     *
     * @param string $content  The markdown content to save.
     * @param string $filename The desired base filename (without extension).
     *
     * @return string The generated filename with extension (not the full path).
     * @throws Exception If the storage directory cannot be created or the file cannot be written.
     */
    public static function saveSaeDescription(string $content, string $filename): string
    {
        if (!is_dir(self::STORAGE_DIR)) {
            if (!mkdir(self::STORAGE_DIR, 0777, true)) {
                throw new Exception("Impossible de créer le dossier de stockage.");
            }
        }

        // Sanitize filename.
        $safeFilename = preg_replace('/[^a-zA-Z0-9_-]/', '', $filename);
        $fileNameWithExt = $safeFilename . '_' . uniqid() . '.md';
        $fullPath = realpath(self::STORAGE_DIR) . '/' . $fileNameWithExt;

        if (file_put_contents($fullPath, $content) === false) {
            throw new Exception("Impossible d'écrire le fichier de description.");
        }

        return $fileNameWithExt;
    }

    /**
     * Removes a file from storage.
     *
     * Checks if the file exists before attempting deletion.
     *
     * @param string $filename The name of the file to remove.
     *
     * @return boolean True if the file was successfully deleted or didn't exist, false on failure.
     * @throws Exception If an error occurs during file deletion (implied by context, though not explicitly thrown).
     */
    public static function removeFile(string $filename): bool
    {

        if (empty($filename)) {
            return false;
        }
        $fullPath = realpath(self::STORAGE_DIR) . '/' . $filename;

        if (!is_file($fullPath)) {
            return false;
        }
        return  unlink($fullPath);
    }
}
