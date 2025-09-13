<?php
function validateField(string $field, $value, array $context): ?string {
    switch ($field) {
        case 'gender':
            return isValidGender($value) ? null : "La civilité n'est pas valide.";

        case 'email':
            if (!isValidEmail($value)) return "L'adresse e-mail n'est pas valide.";
            if (!isOwnEmail($value, $context['lname'] ?? '', $context['fname'] ?? '')) return "Utilisez votre adresse e-mail.";
            return null;

        case 'pwd':
            if (!isValidPassword($value)) return "Le mot de passe n'est pas valide.";
            if ($value !== ($context['pwdverif'] ?? '')) return "Les mots de passe ne correspondent pas.";
            return null;

        case 'tel':
            return isValidPhone($value) ? null : "Le numéro de téléphone n'est pas valide.";

        case 'dob':
            if (!isValidDate($value)) return "La date de naissance n'est pas valide.";
            $age = (new DateTime())->diff(new DateTime($value))->y;
            return ($age < 16) ? "Vous devez avoir au moins 16 ans pour vous inscrire." : null;

        case 'year':
            return isValidYear($value) ? null : "L'année n'est pas valide.";

        case 'parcours':
            $year = $context['year'] ?? '';
            if ($year === '1' && !empty($value)) return "Le parcours ne doit pas être sélectionné en 1ère année.";
            if (in_array($year, ['2', '3'])) {
                if (empty($value)) return "Le parcours doit être sélectionné en 2ème ou 3ème année.";
                if (!isValidParcours($value)) return "Le parcours sélectionné n'est pas valide.";
            }
            return null;

        case 'td':
            if (!isValidTD($value)) return "Le groupe de TD n'est pas valide.";
            if (in_array($context['year'] ?? '', ['2', '3']) && $value === 'TD4') return "Le groupe TD4 ne peut pas être sélectionné en 2e ou 3e année.";
            return null;

        case 'tp':
            return isValidTP($value) ? null : "Le groupe de TP n'est pas valide.";

        case 'terms':
            return ($value === 'on') ? null : "Vous devez accepter les conditions générales.";

        default:
            echo "$value <br>";
            return null;
    }
}
