<?php

namespace App\Domain\Auth;

use Core\Models\BaseEntity;
use DateTime;

class Token extends BaseEntity
{
    private string $email;
    private string $token;
    private DateTime $createdAt;
    private DateTime $expiresAt;
    private bool $used;

    public function __construct(
        string $email,
        string $token,
        DateTime $createdAt,
        DateTime $expiresAt,
        bool $used = false
        )
    {
        $this->email = $email;
        $this->token = $token;
        $this->createdAt = $createdAt;
        $this->expiresAt = $expiresAt;
        $this->used = $used;
    }

    public function getEmail(): string
    {
        return $this->email;
    }
    public function getToken(): string
    {
        return $this->token;
    }
    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }
    public function getExpiresAt(): DateTime
    {
        return $this->expiresAt;
    }
    public function isUsed(): bool
    {
        return $this->used;
    }

    public function markAsUsed(): void
    {
        $this->used = true;
    }

    public function isExpired(): bool
    {
        return $this->expiresAt < new DateTime();
    }
}