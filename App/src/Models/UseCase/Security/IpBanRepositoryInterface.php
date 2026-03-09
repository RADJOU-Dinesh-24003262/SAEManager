<?php

namespace Models\UseCase\Security;

use Models\Entity\Security\IpBan;

/**
 * Interface for IP ban management.
 *
 * @category UseCase
 * @package  Models\UseCase\Security
 * @author   Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license  MIT License https://opensource.org/licenses/MIT
 * @link     https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
interface IpBanRepositoryInterface
{
    /**
     * Checks if the given IP address is currently banned.
     *
     * @param string $ip The IP address to check.
     * @return boolean True if the IP is banned, false otherwise.
     */
    public function isBanned(string $ip): bool;

    /**
     * Bans the given IP address for a certain number of days.
     *
     * @param string  $ip   The IP address to ban.
     * @param integer $days Duration of the ban in days.
     * @return void
     */
    public function banIp(string $ip, int $days = 3): void;

    /**
     * Removes all expired bans from the storage.
     *
     * @return void
     */
    public function removeExpiredBans(): void;
}
