<?php

namespace Models\Entity\Security;

use DateTime;
use Core\Models\BaseModel;

/**
 * Entity representing an IP ban.
 *
 * @category Entity
 * @package  Models\Entity\Security
 * @author   Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license  MIT License https://opensource.org/licenses/MIT
 * @link     https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class IpBan extends BaseModel
{
    /**
     * @var string The banned IP address.
     */
    protected string $ip;

    /**
     * @var DateTime The expiration date of the ban.
     */
    protected DateTime $expiresAt;

    /**
     * Constructor to initialize the IpBan entity.
     *
     * @param array{ip?: string, expiresAt?: string|DateTime} $data The data to initialize the entity.
     */
    public function __construct(array $data = [])
    {
        $this->ip = $data['ip'] ?? '';
        if (isset($data['expiresAt'])) {
            if ($data['expiresAt'] instanceof DateTime) {
                $this->expiresAt = $data['expiresAt'];
            } else {
                $this->expiresAt = new DateTime($data['expiresAt']);
            }
        } else {
            // Default to 3 days from now.
            $this->expiresAt = (new DateTime())->modify('+3 days');
        }
    }

    /**
     * Gets the IP address.
     *
     * @return string
     */
    public function getIp(): string
    {
        return $this->ip;
    }

    /**
     * Gets the expiration date.
     *
     * @return DateTime
     */
    public function getExpiresAt(): DateTime
    {
        return $this->expiresAt;
    }

    /**
     * Implementing abstract method from BaseModel.
     * Since IpBan is not stored with a numeric ID in JSON, returns null.
     *
     * @return integer|null
     */
    public function getId(): ?int
    {
        return null;
    }

    /**
     * Checks if the ban has expired.
     *
     * @return boolean
     */
    public function isExpired(): bool
    {
        return new DateTime() > $this->expiresAt;
    }

    /**
     * Converts the entity to an array.
     *
     * @return array{ip: string, expiresAt: string}
     */
    public function toArray(): array
    {
        return [
            'ip' => $this->ip,
            'expiresAt' => $this->expiresAt->format('Y-m-d H:i:s'),
        ];
    }
}
