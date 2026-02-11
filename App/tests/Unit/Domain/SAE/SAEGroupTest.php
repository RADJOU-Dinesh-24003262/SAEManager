<?php

namespace Tests\Unit\Domain\SAE;

use App\Domain\SAE\SaeGroup;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(SAEGroup::class)]
class SAEGroupTest extends TestCase
{
    #[Test]
    public function canBeInstantiatedWithData(): void
    {
        $saeGroupId = 1;
        $saeSubjectId = 10;
        $professorId = 5;

        $group = new SAEGroup($saeGroupId, $saeSubjectId, $professorId);

        $this->assertEquals($saeGroupId, $group->getId());
        $this->assertEquals($saeSubjectId, $group->getSaeId());
        $this->assertEquals($professorId, $group->getProfessorId());
    }

    #[Test]
    public function canHandleNullableProfessor(): void
    {
        $saeGroupId = 1;
        $saeSubjectId = 10;
        $professorId = null;

        $group = new SAEGroup($saeGroupId, $saeSubjectId, $professorId);

        $this->assertNull($group->getProfessorId());
    }

    #[Test]
    public function toArrayReturnsCorrectData(): void
    {
        $data = [
            'sae_group_id' => 1,
            'sae_subject_id' => 10,
            'professor_id' => 5
        ];

        $group = new SAEGroup(1, 10, 5);
        $this->assertEquals($data, $group->toArray());
    }
}