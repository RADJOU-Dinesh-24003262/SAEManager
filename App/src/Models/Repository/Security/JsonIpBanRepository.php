<?php

namespace Models\Repository\Security;

use Models\Entity\Security\IpBan;
use Models\UseCase\Security\IpBanRepositoryInterface;
use DateTime;

/**
 * Concrete implementation of IpBanRepositoryInterface using a JSON file.
 *
 * @category Repository
 * @package  Models\Repository\Security
 * @author   Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license  MIT License https://opensource.org/licenses/MIT
 * @link     https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class JsonIpBanRepository implements IpBanRepositoryInterface
{
    /**
     * @var string The path to the JSON storage file.
     */
    private string $storagePath;

    /**
     * Constructor.
     *
     * @param string $storagePath Optional path to the storage file.
     */
    public function __construct(string $storagePath = __DIR__ . '/../../../../../storage/ip_bans.json')
    {
        $this->storagePath = $storagePath;
        $this->ensureStorageExists();
    }

    /**
     * Ensures the storage file and directory exist.
     *
     * @return void
     */
    private function ensureStorageExists(): void
    {
        $dir = dirname($this->storagePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        if (!file_exists($this->storagePath)) {
            file_put_contents($this->storagePath, json_encode([]));
        }
    }

    /**
     * Checks if the given IP is banned.
     *
     * @param string $ip The IP address to check.
     * @return boolean True if banned, false otherwise.
     */
    public function isBanned(string $ip): bool
    {
        $bans = $this->loadBans();
        if (!isset($bans[$ip])) {
            return false;
        }

        $ipBan = new IpBan(['ip' => $ip, 'expiresAt' => $bans[$ip]]);
        if ($ipBan->isExpired()) {
            $this->removeBan($ip);
            return false;
        }

        return true;
    }

    /**
     * Bans an IP.
     *
     * @param string  $ip   The IP address to ban.
     * @param integer $days The duration of the ban in days.
     * @return void
     */
    public function banIp(string $ip, int $days = 3): void
    {
        $expiresAt = (new DateTime())->modify("+$days days")->format('Y-m-d H:i:s');
        $bans = $this->loadBans();
        $bans[$ip] = $expiresAt;
        $this->saveBans($bans);
    }

    /**
     * Removes expired bans.
     *
     * @return void
     */
    public function removeExpiredBans(): void
    {
        $bans = $this->loadBans();
        $now = new DateTime();
        $newBans = [];
        foreach ($bans as $ip => $expiry) {
            if (new DateTime($expiry) > $now) {
                $newBans[$ip] = $expiry;
            }
        }
        $this->saveBans($newBans);
    }

    /**
     * Removes a specific IP ban.
     *
     * @param string $ip The IP address to remove from the ban list.
     * @return void
     */
    private function removeBan(string $ip): void
    {
        $bans = $this->loadBans();
        if (isset($bans[$ip])) {
            unset($bans[$ip]);
            $this->saveBans($bans);
        }
    }

    /**
     * Loads bans from JSON file.
     *
     * @return array<string, string>
     */
    private function loadBans(): array
    {
        $content = file_get_contents($this->storagePath);
        if (!$content) {
            return [];
        }
        return json_decode($content, true) ?: [];
    }

    /**
     * Saves bans to JSON file.
     *
     * @param array<string, string> $bans The list of bans to save.
     * @return void
     */
    private function saveBans(array $bans): void
    {
        file_put_contents($this->storagePath, json_encode($bans, JSON_PRETTY_PRINT));
    }
}
