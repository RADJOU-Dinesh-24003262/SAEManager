<?php
namespace Utilis;

/**
 * Class SessionService
 
 * @package     src

 * @subpackage  Utilis

 * @author      Benhafessa Alexandre, Dargentolle Francois, Edelstein William, Griguer Nathan, Radjou Dinesh

 * This class regroup function to manage the user session.
 */
class SessionService
{
    /**
     *
     * 
     * This method starts a session if none exists.
     * 
     * @return void
     */
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * 
     * 
     * This method creates a key value pair with the key and value given in parametters in the session.
     * 
     * @param $key the key to set the value of.
     * @param $value the value that will be set.
     * 
     * @return void
     */
    public static function set(string $key, $value): void
    {
        self::start();
        $_SESSION[$key] = $value;
    }

    /**
     * Returns the value assioated to the key given in parametters.
     * 
     * This method returns the assiociated value to the key given in parametters in the user session.
     * 
     * @param $key the key to get the message of.
     * @param $default used for genericity
     * 
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Returns the state of a value in the session
     * 
     * This method returns true if the session key given in parametters is set. False otherwise.
     * 
     * @param $key the key to check the set state of a session value pair
     * 
     * @return boolean
     */
    public static function has(string $key): bool
    {
        self::start();
        return isset($_SESSION[$key]);
    }

    /**
     * 
     * 
     * This method unsets in the user session the key given in parametters.
     * 
     * @return void
     */
    public static function remove(string $key): void
    {
        self::start();
        unset($_SESSION[$key]);
    }

    /**
     * 
     * 
     * This method created a flash message with the key and value given in parametters. It is set into the user session.
     * 
     * @return void
     */
    public static function setFlash(string $key, $value): void
    {
        self::start();
        $_SESSION['flash'][$key] = $value;
    }

    /**
     * 
     * 
     * This method gets the flash message with the key given in parametter, unsets and returns it.
     * 
     * @param $key the key to get the flash message of.
     * @param $default used for genericity.
     * 
     * @return mixed
     */
    public static function getFlash(string $key, $default = null)
    {
        self::start();
        $value = $_SESSION['flash'][$key] ?? $default;
        unset($_SESSION['flash'][$key]);
        return $value;
    }

    /**
     * 
     * 
     * This method returns true if the session flash message key given in parametters is set. False otherwise.
     * 
     * @param $key the key to get the flash message of.
     * 
     * @return boolean
     */
    public static function hasFlash(string $key): bool
    {
        self::start();
        return isset($_SESSION['flash'][$key]);
    }

    /**
     * 
     * 
     * This methods destroys the user session.
     * 
     * @return void
     */
    public static function destroy(): void
    {
        self::start();
        session_destroy();
    }

    /**
     * 
     * 
     * This methods destroys the user session and recreated a new one with a new id (keeping it's stored key value pairs).
     * 
     * @return void
     */
    public static function regenerateId(): void
    {
        self::start();
        session_regenerate_id(true);
    }
}