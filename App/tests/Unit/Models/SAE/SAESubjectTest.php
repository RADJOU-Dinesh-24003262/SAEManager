<?php

namespace Tests\Unit\Models\SAE;

use Models\SAE\SAESubject;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use DateTime;

#[CoversClass(SAESubject::class)]
class SAESubjectTest extends TestCase
{
    #[Test]
    public function canBeInstantiatedWithData(): void
    {
        $data = [
            'subject_name' => 'Test SAE',
            'responsible_prof_id' => 1,
            'client_id' => 2,
            'begin_date' => '2023-01-01',
            'end_date' => '2023-06-01',
            'file_path' => '/tmp/test.md'
        ];

        $sae = new SAESubject($data);

        $this->assertEquals('Test SAE', $sae->getSubjectName());
        $this->assertEquals(1, $sae->getResponsibleProfId());
        $this->assertEquals(2, $sae->getClientId());
        $this->assertEquals('2023-01-01', $sae->getBeginDate());
        $this->assertEquals('2023-06-01', $sae->getEndDate());
        $this->assertEquals('/tmp/test.md', $sae->getFilePath());
    }

    #[Test]
    public function canHandleNullableClient(): void
    {
        $data = [
            'subject_name' => 'Test SAE',
            'responsible_prof_id' => 1,
            'client_id' => null, // Testing nullable client
            'begin_date' => '2023-01-01',
            'end_date' => '2023-06-01'
        ];

        $sae = new SAESubject($data);

        $this->assertNull($sae->getClientId());
    }

    #[Test]
    public function isActiveReturnsTrueForCurrentDates(): void
    {
        $today = new DateTime();
        $tomorrow = (new DateTime())->modify('+1 day')->format('Y-m-d');
        $yesterday = (new DateTime())->modify('-1 day')->format('Y-m-d');

        $sae = new SAESubject([
            'begin_date' => $yesterday,
            'end_date' => $tomorrow
        ]);

        $this->assertTrue($sae->isActive());
    }

    #[Test]
    public function isActiveReturnsFalseForPastDates(): void
    {
        $pastStart = (new DateTime())->modify('-10 days')->format('Y-m-d');
        $pastEnd = (new DateTime())->modify('-5 days')->format('Y-m-d');

        $sae = new SAESubject([
            'begin_date' => $pastStart,
            'end_date' => $pastEnd
        ]);

        $this->assertFalse($sae->isActive());
    }

    #[Test]
    public function isActiveReturnsFalseForFutureDates(): void
    {
        $futureStart = (new DateTime())->modify('+5 days')->format('Y-m-d');
        $futureEnd = (new DateTime())->modify('+10 days')->format('Y-m-d');

        $sae = new SAESubject([
            'begin_date' => $futureStart,
            'end_date' => $futureEnd
        ]);

        $this->assertFalse($sae->isActive());
    }

    #[Test]
    public function getDaysRemainingCalculatesCorrectly(): void
    {
        $futureEnd = (new DateTime())->modify('+5 days');
        $sae = new SAESubject([
            'end_date' => $futureEnd->format('Y-m-d')
        ]);

        // Note: diff might vary by +/- 1 depending on time of day execution, so we allow a small margin or set time to midnight if we could modify the class logic easily.
        // Assuming the class uses new DateTime() which takes current time.

        $expected = (int) (new DateTime())->diff($futureEnd)->days;
        // Since getDaysRemaining returns signed int and future is positive

        // Let's force consistent time for testing if possible, but we can't easily mock DateTime inside the class without DI.
        // We will check if it's close enough (4 or 5 days depending on hour)
        $this->assertGreaterThanOrEqual(4, $sae->getDaysRemaining());
        $this->assertLessThanOrEqual(6, $sae->getDaysRemaining());
    }


    #[Test]
    public function toArrayReturnsCorrectData(): void
    {
        $data = [
            'sae_subject_id' => 10,
            'responsible_prof_id' => 1,
            'client_id' => null,
            'subject_name' => 'Test',
            'begin_date' => '2023-01-01',
            'end_date' => '2023-02-01',
            'file_path' => null
        ];

        $sae = new SAESubject($data);
        $array = $sae->toArray();

        $this->assertEquals($data, $array);
    }
}
