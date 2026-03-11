<?php

namespace Services;

use Core\Includes\Database;
use Core\Includes\Exception\ExceptionToken\ExceptionCreationTokenFailed;
use Core\Includes\Exception\ExceptionToken\ExceptionInvalidToken;
use Core\Includes\Exception\ExceptionSpam;
use PDO;
use PDOException;

/**
 * Class TokenService
 *
 * This class regroups the functions to manage reset password tokens.
 * It can generate, update, delete, and check the validity of tokens.
 * Requires email integration to function properly.
 *
 * @category   Services
 * @package    Src
 * @subpackage App/Services
 * @author     Radjou Dinesh <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class TokenService
{
    /**
     * Returns a 64-character long secure random hexadecimal string.
     *
     * @return string
     */
    public static function generate(): string
    {
        return bin2hex(random_bytes(32)); // 64 hexadecimal characters.
    }

    
}
