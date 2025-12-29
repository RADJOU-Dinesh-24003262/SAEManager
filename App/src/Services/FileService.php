<?php

namespace Services;

/**
 * Service to handle file operations.
 *
 * @category Services
 * @package  Src
 * @subpackage App/Services
 * @author     Dinesh Radjou <dinesh.radjou@univ-amu.fr>
 * @license    https://opensource.org/licenses/MIT MIT License
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager/blob/main/App/src/Services/FileService.php
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
     * @param string $content  The markdown content.
     * @param string $filename The desired filename (without extension).
     * @return string The absolute path to the saved file.
     * @throws \Exception If file cannot be saved.
     */
    public static function saveSaeDescription(string $content, string $filename): string
    {
        if (!is_dir(self::STORAGE_DIR)) {
            if (!mkdir(self::STORAGE_DIR, 0777, true)) {
                throw new \Exception("Impossible de créer le dossier de stockage.");
            }
        }

        // Sanitize filename.
        $safeFilename = preg_replace('/[^a-zA-Z0-9_-]/', '', $filename);
        $fileNameWithExt = $safeFilename . '_' . uniqid() . '.md';
        $fullPath = realpath(self::STORAGE_DIR) . '/' . $fileNameWithExt;

        if (file_put_contents($fullPath, $content) === false) {
            throw new \Exception("Impossible d'écrire le fichier de description.");
        }

        return $fileNameWithExt;
    }
}
