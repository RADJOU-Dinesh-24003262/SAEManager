<?php

namespace Tests\Unit\Models\SAE;

use Models\SAE\SAEGroup;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(SAEGroup::class)]
class SAEGroupTest extends TestCase
{
    #[Test]
    public function canBeInstantiatedWithData(): void
    {
        $data = [
            'sae_group_id' => 1,
            'sae_subject_id' => 10,
            'professor_id' => 5
        ];

        $group = new SAEGroup($data);

        $this->assertEquals(1, $group->getSaeGroupId());
        $this->assertEquals(10, $group->getSaeSubjectId());
        $this->assertEquals(5, $group->getProfessorId());
    }

    #[Test]
    public function canHandleNullableProfessor(): void
    {
        $data = [
            'sae_subject_id' => 10,
            'professor_id' => null
        ];

        $group = new SAEGroup($data);

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

        $group = new SAEGroup($data);
        $this->assertEquals($data, $group->toArray());
    }
}
