<?php

namespace Tests\Unit\Models\Repository\Security;

use PHPUnit\Framework\TestCase;
use Models\Repository\Security\JsonIpBanRepository;
use Models\Entity\Security\IpBan;
use DateTime;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;

#[CoversClass(JsonIpBanRepository::class)]
#[UsesClass(IpBan::class)]
class JsonIpBanRepositoryTest extends TestCase
{
    private string $testFile;
    private JsonIpBanRepository $repository;

    protected function setUp(): void
    {
        $this->testFile = __DIR__ . '/test_ip_bans.json';
        if (file_exists($this->testFile)) {
            unlink($this->testFile);
        }
        $this->repository = new JsonIpBanRepository($this->testFile);
    }

    protected function tearDown(): void
    {
        if (file_exists($this->testFile)) {
            unlink($this->testFile);
        }
    }

    public function testBanIpSavesToJson()
    {
        $ip = '127.0.0.1';
        $this->repository->banIp($ip, 3);

        $this->assertTrue($this->repository->isBanned($ip));
        $this->assertFileExists($this->testFile);

        $content = json_decode(file_get_contents($this->testFile), true);
        $this->assertArrayHasKey($ip, $content);
    }

    public function testIsBannedReturnsFalseForNonBannedIp()
    {
        $this->assertFalse($this->repository->isBanned('1.2.3.4'));
    }

    public function testExpiredBanIsRemoved()
    {
        $ip = '10.0.0.1';
        // Manually save an expired ban
        $expiredDate = (new DateTime())->modify('-1 day')->format('Y-m-d H:i:s');
        file_put_contents($this->testFile, json_encode([$ip => $expiredDate]));

        $this->assertFalse($this->repository->isBanned($ip));

        $content = json_decode(file_get_contents($this->testFile), true);
        $this->assertArrayNotHasKey($ip, $content);
    }

    public function testRemoveExpiredBansCleansFile()
    {
        $activeIp = '1.1.1.1';
        $expiredIp = '2.2.2.2';

        $activeDate = (new DateTime())->modify('+1 day')->format('Y-m-d H:i:s');
        $expiredDate = (new DateTime())->modify('-1 day')->format('Y-m-d H:i:s');

        file_put_contents($this->testFile, json_encode([
            $activeIp => $activeDate,
            $expiredIp => $expiredDate
        ]));

        $this->repository->removeExpiredBans();

        $content = json_decode(file_get_contents($this->testFile), true);
        $this->assertArrayHasKey($activeIp, $content);
        $this->assertArrayNotHasKey($expiredIp, $content);
    }
}
