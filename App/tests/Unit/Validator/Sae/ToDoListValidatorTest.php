<?php

namespace Tests\Unit\Validator\Sae;

use Core\Includes\Exception\ExceptionValidation\ExceptionValidationToDoList;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Validator\Sae\ToDoListValidator;

/**
 * Unit test for ToDoListValidator class filtering exceptions written by François Dargentolle.
 *
 * @category Tests
 * @package  Tests\Unit\Validator\Sae
 * @author   François Dargentolle
 */
#[CoversClass(ExceptionValidationToDoList::class)]
#[CoversClass(ToDoListValidator::class)]
class ToDoListValidatorTest extends TestCase
{
    private ToDoListValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new ToDoListValidator();
    }

    /**
     * Test validation throws ExceptionValidationToDoList when description is empty.
     *
     * @return void
     */
    public function testValidateThrowsExceptionWhenDescriptionIsEmpty(): void
    {
        $this->expectException(ExceptionValidationToDoList::class);
        $this->expectExceptionMessage("La description ne doit pas être vide.");

        $data = [
            'description' => '   ',
        ];

        $this->validator->validate($data);
    }

    /**
     * Test validation throws ExceptionValidationToDoList when description exceeds 255 characters.
     *
     * @return void
     */
    public function testValidateThrowsExceptionWhenDescriptionIsTooLong(): void
    {
        $this->expectException(ExceptionValidationToDoList::class);
        $this->expectExceptionMessage("La description ne doit pas dépasser 255 caractères.");

        $data = [
            'description' => str_repeat('a', 256),
        ];

        $this->validator->validate($data);
    }

    /**
     * Test validation throws ExceptionValidationToDoList when priority is invalid.
     *
     * @return void
     */
    public function testValidateThrowsExceptionWhenPriorityIsInvalid(): void
    {
        $this->expectException(ExceptionValidationToDoList::class);
        $this->expectExceptionMessage("Priorité invalide (doit être 1, 2 ou 3).");

        $data = [
            'description' => 'Valid description', // Needed if we also test priority, although the validator checks sequentially
            'priority' => 4,
        ];

        $this->validator->validate($data);
    }
}
