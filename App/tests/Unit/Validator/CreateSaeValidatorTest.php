<?php

namespace Tests\Unit\Validator;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\CoversClass;
use Validator\CreateSaeValidator;
use Validator\FormValidator;
use Core\includes\exception\ExceptionValidation\ExeptionValidationSAECreation;

#[CoversClass(CreateSaeValidator::class)]
#[CoversClass(FormValidator::class)]
#[CoversClass(ExeptionValidationSAECreation::class)]
class CreateSaeValidatorTest extends TestCase
{
    private CreateSaeValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new CreateSaeValidator();
    }

    private function getValidData(): array
    {
        return [
            'subject_name' => 'Valid Name',
            'description' => 'Valid description with enough characters',
            'begin_date' => '2023-01-01',
            'end_date' => '2023-02-01',
            'client_id' => '1'
        ];
    }

    #[Test]
    public function validDataPassesValidation(): void
    {
        $this->expectNotToPerformAssertions();
        $this->validator->validate($this->getValidData());
    }

    #[Test]
    public function shortNameThrowsException(): void
    {
        $data = $this->getValidData();
        $data['subject_name'] = 'AB'; // Less than 3 chars

        $this->expectException(ExeptionValidationSAECreation::class);
        $this->expectExceptionMessage('Le nom de la SAE doit faire entre 3 et 255 caractères.');

        $this->validator->validate($data);
    }

    #[Test]
    public function shortDescriptionThrowsException(): void
    {
        $data = $this->getValidData();
        $data['description'] = 'Short'; // Less than 10 chars

        $this->expectException(ExeptionValidationSAECreation::class);
        $this->expectExceptionMessage('La description doit contenir au moins 10 caractères.');

        $this->validator->validate($data);
    }

    #[Test]
    public function invalidBeginDateThrowsException(): void
    {
        $data = $this->getValidData();
        $data['begin_date'] = 'invalid-date';

        $this->expectException(ExeptionValidationSAECreation::class);
        $this->expectExceptionMessage('La date de début n\'est pas valide.');

        $this->validator->validate($data);
    }

    #[Test]
    public function invalidDateRenduThrowsException(): void
    {
        $data = $this->getValidData();
        $data['end_date'] = '2023-13-45';

        $this->expectException(ExeptionValidationSAECreation::class);
        $this->expectExceptionMessage('La date de rendu n\'est pas valide.');

        $this->validator->validate($data);
    }

    #[Test]
    public function dateRenduBeforeBeginDateThrowsException(): void
    {
        $data = $this->getValidData();
        $data['begin_date'] = '2023-02-01';
        $data['end_date'] = '2023-01-01'; // Before begin

        $this->expectException(ExeptionValidationSAECreation::class);
        $this->expectExceptionMessage('La date de rendu doit être postérieure à la date de début.');

        $this->validator->validate($data);
    }

    #[Test]
    public function endDateBeforeBeginDateThrowsException(): void
    {
        $data = $this->getValidData();
        $data['begin_date'] = '2024-02-01';
        $data['end_date'] = '2023-01-01'; // Invalid (before begin)

        $this->expectException(ExeptionValidationSAECreation::class);
        $this->expectExceptionMessage('La date de rendu doit être postérieure à la date de début.');

        $this->validator->validate($data);
    }

    #[Test]
    public function invalidClientIdThrowsException(): void
    {
        $data = $this->getValidData();
        $data['client_id'] = '-5';

        $this->expectException(ExeptionValidationSAECreation::class);
        $this->expectExceptionMessage('L\'identifiant client est invalide.');

        $this->validator->validate($data);
    }
}
