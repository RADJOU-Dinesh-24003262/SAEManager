<?php

namespace Validator;

use Core\includes\exception\ExceptionValidation\ExeptionValidationSAECreation;
use Override;
use DateTime;

/**
 * Class SaeSujetValidator
 * Validates the SAE subject creation form.
 *
 * @category Validator
 * @package  Src
 * @subpackage Validator
 * @author     Dinesh Radjou <dinesh.radjou@univ-amu.fr>
 * @license    https://opensource.org/licenses/MIT MIT License
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager/blob/main/App/src/Validator/CreateSaeValidator.php
 */
class CreateSaeValidator extends FormValidator
{
    /**
     * List of required fields.
     *
     * @var array<string>
     */
    protected $required = [
        'nameSae',
        'end_date',
        'begin_date',
        'description'
    ];

    /**
     * Validates the SAE subject form data.
     *
     * @param array<string, mixed> $data The form data.
     * @return void
     * @throws ExeptionValidationSAECreation If validation fails.
     */
    #[Override]
    public function validate(array $data): void
    {
        // Validate name length.
        if (strlen($data['nameSae']) < 3 || strlen($data['nameSae']) > 255) {
            throw new ExeptionValidationSAECreation('Le nom de la SAE doit faire entre 3 et 255 caractères.');
        }

        // Validate description length.
        if (strlen($data['description']) < 10) {
            throw new ExeptionValidationSAECreation('La description doit contenir au moins 10 caractères.');
        }

        // Validate date format.
        if (!$this->isValidDate($data['end_date'])) {
            throw new ExeptionValidationSAECreation('La date de rendu n\'est pas valide.');
        }

        if (!$this->isValidDate($data['begin_date'])) {
            throw new ExeptionValidationSAECreation('La date de début n\'est pas valide.');
        }

        // Check if dates are logical (end > begin).
        if ($data['end_date'] <= $data['begin_date']) {
            throw new ExeptionValidationSAECreation('La date de rendu doit être postérieure à la date de début.');
        }

        // Validate client ID (positive integer check).
        if (!empty($data['client_id'])) {
            $clientId = filter_var($data['client_id'], FILTER_VALIDATE_INT);
            if ($clientId === false || $clientId <= 0) {
                throw new ExeptionValidationSAECreation('L\'identifiant client est invalide.');
            }
        }

        $begin = new DateTime($data['begin_date']);
        $end = new DateTime($data['end_date']);

        if ($end <= $begin) {
            throw new ExeptionValidationSAECreation('La date de fin doit être après la date de début');
        }
    }
}
