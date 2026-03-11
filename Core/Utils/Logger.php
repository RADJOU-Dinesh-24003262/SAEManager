<?php

namespace Core\Utils;

/**
 * Class Logger
 * Handles security logging with structured output.
 *
 * @category Utils
 * @package  Core\Utils
 * @author   Alexandre Benhafessa <alexandre.benhafessa@etu.univ-amu.fr>
 * @author   François Dargentolle <francois.dargentolle@etu.univ-amu.fr>
 * @author   William Edelstein <william.edelstein@etu.univ-amu.fr>
 * @author   Nathan Griguer <nathan.griguer@etu.univ-amu.fr>
 * @author   Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license  MIT License https://opensource.org/licenses/MIT
 * @link     https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class Logger
{
    private const LOG_DIR = __DIR__ . '/../../logs/';
    private const SECURITY_LOG = self::LOG_DIR . 'security.log';
    private const MAIL_LOG = self::LOG_DIR . 'mail.log';
    private const SAE_LOG = self::LOG_DIR . 'sae.log';

    /**
     * Log a security event.
     *
     * @param string       $action  The action being performed (e.g., 'LOGIN_ATTEMPT', 'CSRF_FAIL', 'MAIL_SEND').
     * @param string       $details Details about the event.
     * @param integer|null $userId  Optional User ID associated with the event.
     * @param string       $level   Severity level (INFO, WARNING, CRITICAL).
     *
     * @return void
     */
    public static function log(string $action, string $details, ?int $userId = null, string $level = 'INFO'): void
    {
        $date = date('Y-m-d H:i:s');
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
        $userStr = $userId ? "User:$userId" : "Guest";

        // Format: [DATE] [LEVEL] [IP] [USER] [ACTION] Details.
        $logMessage = sprintf(
            "[%s] [%s] [%s] [%s] [%s] %s" . PHP_EOL,
            $date,
            str_pad($level, 8),
            str_pad($ip, 15),
            str_pad($userStr, 10),
            str_pad($action, 20),
            $details
        );

        // Determine log file based on action prefix.
        $targetFile = self::SECURITY_LOG;
        if (str_starts_with($action, 'MAIL_')) {
            $targetFile = self::MAIL_LOG;
        }
        if (str_starts_with($action, 'SAE_')) {
            $targetFile = self::SAE_LOG;
        }

        // Ensure directory exists.
        if (!is_dir(dirname($targetFile))) {
            mkdir(dirname($targetFile), 0755, true);
        }

        // Append to file.
        file_put_contents($targetFile, $logMessage, FILE_APPEND);
    }
}
