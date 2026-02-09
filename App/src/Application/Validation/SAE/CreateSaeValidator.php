<?php

namespace App\Application\Validation\SAE;

use App\Application\Validation\AbstractValidator;
use App\Application\Validation\Exception\SaeCreationValidationException;
use App\Application\Validation\Rules\DateRule;
use DateTime;
use Override;

/**
 * Validator for SAE subject creation.
 * Validates SAE-specific fields: name, description, dates, client ID.
 *
 * @category Validation
 * @package  App\Application\Validation\SAE
 */
class CreateSaeValidator extends AbstractValidator
{
    protected array $required = ['nameSae', 'description', 'date_rendu', 'begin_date', 'end_date'];

    #[Override]
    public function validate(array $data): void
    {
        $this->checkRequired($data);

        // Validate name length
        if (strlen($data['nameSae']) < 3 || strlen($data['nameSae']) > 255) {
            throw new SaeCreationValidationException('Le nom de la SAE doit faire entre 3 et 255 caractères.');
        }

        // Validate description length
        if (strlen($data['description']) < 10) {
            throw new SaeCreationValidationException('La description doit contenir au moins 10 caractères.');
        }

        // Validate date formats
        $dateRule = new DateRule();
        
        if (!$dateRule->validate($data['date_rendu'])) {
            throw new SaeCreationValidationException("La date de rendu n'est pas valide.");
        }

        if (!$dateRule->validate($data['begin_date'])) {
            throw new SaeCreationValidationException("La date de début n'est pas valide.");
        }

        if (!$dateRule->validate($data['end_date'])) {
            throw new SaeCreationValidationException("La date de fin n'est pas valide.");
        }

        // Check if dates are logical (end > begin, rendu > begin)
        if ($data['date_rendu'] <= $data['begin_date']) {
            throw new SaeCreationValidationException('La date de rendu doit être postérieure à la date de début.');
        }

        $begin = new DateTime($data['begin_date']);
        $end = new DateTime($data['end_date']);

        if ($end <= $begin) {
            throw new SaeCreationValidationException('La date de fin doit être après la date de début.');
        }

        // Validate client ID (positive integer check)
        if (!empty($data['client_id'])) {
            $clientId = filter_var($data['client_id'], FILTER_VALIDATE_INT);
            if ($clientId === false || $clientId <= 0) {
                throw new SaeCreationValidationException("L'identifiant client est invalide.");
            }
        }
    }
}