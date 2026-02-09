<?php

namespace App\Application\Validation\User;

use App\Application\Validation\AbstractValidator;
use App\Application\Validation\Exception\RegisterValidationException;
use App\Application\Validation\Exception\RegisterValidationsException;
use App\Application\Validation\Rules\{
    EmailRule,
    PasswordRule,
    PhoneRule,
    UserTypeRule,
    AmuIdRule,
    AmuPrefixRule,
    YearRule,
    MajorRule,
    TDRule,
    TPRule
};
use Override;

/**
 * Validator for student registration.
 * Validates student-specific fields: AMU ID, year, major, TD, TP groups.
 *
 * @category Validation
 * @package  App\Application\Validation\User
 */
class RegisterStudentValidator extends AbstractValidator
{
    protected array $required = [
        'first_name', 'last_name', 'user_type', 'email',
        'password', 'passwordverif', 'phone', 'terms',
        'amu_id', 'year', 'td', 'tp'
    ];

    #[Override]
    public function validate(array $data): void
    {
        $this->checkRequired($data);
        
        $errors = [];

        // User type must be 'student'
        if (($data['user_type'] ?? '') !== 'student') {
            $errors[] = new RegisterValidationException('user_type', 'string', "Type d'utilisateur invalide.");
        }

        // Email (AMU prefix format)
        $amuPrefixRule = new AmuPrefixRule($data['first_name'], $data['last_name']);
        if (!$amuPrefixRule->validate($data['email'])) {
            $errors[] = new RegisterValidationException('email', 'string', $amuPrefixRule->getMessage());
        }

        // Password
        $passwordRule = new PasswordRule();
        if (!$passwordRule->validate($data['password'])) {
            $errors[] = new RegisterValidationException('password', 'string', $passwordRule->getMessage());
        }

        // Password verification
        if ($data['password'] !== ($data['passwordverif'] ?? '')) {
            $errors[] = new RegisterValidationException(
                'passwordverif',
                'string',
                'Les mots de passe ne correspondent pas.'
            );
        }

        // Phone
        $phoneRule = new PhoneRule();
        if (!$phoneRule->validate($data['phone'])) {
            $errors[] = new RegisterValidationException('phone', 'string', $phoneRule->getMessage());
        }

        // AMU ID
        $amuIdRule = new AmuIdRule();
        if (!$amuIdRule->validate($data['amu_id'])) {
            $errors[] = new RegisterValidationException('amu_id', 'string', $amuIdRule->getMessage());
        }

        // Year
        $yearRule = new YearRule();
        if (!$yearRule->validate($data['year'])) {
            $errors[] = new RegisterValidationException('year', 'string', $yearRule->getMessage());
        }

        // Major (required for year 2 and 3)
        if (in_array($data['year'] ?? '', ['2', '3'], true)) {
            if (empty($data['major'])) {
                $errors[] = new RegisterValidationException('major', 'string', 'Parcours requis en BUT 2 et BUT 3.');
            } else {
                $majorRule = new MajorRule();
                if (!$majorRule->validate($data['major'])) {
                    $errors[] = new RegisterValidationException('major', 'string', $majorRule->getMessage());
                }
                
                // TD4 not allowed for year 2 and 3
                if (($data['td'] ?? '') === 'TD4') {
                    $errors[] = new RegisterValidationException('td', 'string', 'TD4 uniquement disponible en BUT 1.');
                }
            }
        } elseif (!empty($data['major'])) {
            $errors[] = new RegisterValidationException(
                'major',
                'string',
                "Le parcours n'est pas applicable pour cette année."
            );
        }

        // TD
        $tdRule = new TDRule();
        if (!$tdRule->validate($data['td'])) {
            $errors[] = new RegisterValidationException('td', 'string', $tdRule->getMessage());
        }

        // TP
        $tpRule = new TPRule();
        if (!$tpRule->validate($data['tp'])) {
            $errors[] = new RegisterValidationException('tp', 'string', $tpRule->getMessage());
        }

        if (!empty($errors)) {
            throw new RegisterValidationsException($errors);
        }
    }
}