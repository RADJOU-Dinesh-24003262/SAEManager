<?php

namespace Core\Utils;

use Core\Utils\SessionService;

/**
 * Utility class to handle rate limiting based on Session.
 * This helps prevent brute-force attacks and spamming.
 *
 * @category Utils
 * @package  Core\Utils
 * @author   Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license  MIT License https://opensource.org/licenses/MIT
 * @link     https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class RateLimiter
{
    /**
     * Checks if the given action has exceeded the allowed attempts.
     * Starts tracking if not already present.
     *
     * @param string  $action      The identifier for the action (e.g., 'login', 'forgot_password').
     * @param integer $maxAttempts Maximum number of allowed attempts within the cooldown period.
     * @param integer $cooldown    Cooldown period in seconds before attempts are reset.
     *
     * @return boolean True if allowed to proceed, false if rate limited.
     */
    public static function check(string $action, int $maxAttempts, int $cooldown): bool
    {
        $sessionKey = 'rate_limit_' . $action;

        if (!SessionService::has($sessionKey)) {
            // First time this action is checked.
            return true;
        }

        $rateData = SessionService::get($sessionKey);

        // Ensure data exists and format is correct (array with attempts & first_attempt).
        if (!is_array($rateData) || !isset($rateData['attempts']) || !isset($rateData['first_attempt'])) {
            return true;
        }

        $timePassed = time() - $rateData['first_attempt'];

        if ($timePassed > $cooldown) {
            // Cooldown period passed, clear the restrict limit.
            self::clear($action);
            return true;
        }

        if ($rateData['attempts'] >= $maxAttempts) {
            return false; // Rate limit exceeded.
        }

        return true;
    }

    /**
     * Increments the attempt counter for a specific action.
     * Initializes the tracking if it does not exist.
     *
     * @param string $action The identifier for the action.
     *
     * @return void
     */
    public static function increment(string $action): void
    {
        $sessionKey = 'rate_limit_' . $action;

        if (!SessionService::has($sessionKey)) {
            SessionService::set($sessionKey, [
                'attempts' => 1,
                'first_attempt' => time(),
            ]);
            return;
        }

        $rateData = SessionService::get($sessionKey);

        if (is_array($rateData) && isset($rateData['attempts'])) {
            $rateData['attempts']++;
            SessionService::set($sessionKey, $rateData);
        }
    }

    /**
     * Clears the rate limit tracking for a specific action.
     * Usually called upon success to reset the counter.
     *
     * @param string $action The identifier for the action.
     *
     * @return void
     */
    public static function clear(string $action): void
    {
        $sessionKey = 'rate_limit_' . $action;
        if (SessionService::has($sessionKey)) {
            SessionService::remove($sessionKey);
        }
    }
}
