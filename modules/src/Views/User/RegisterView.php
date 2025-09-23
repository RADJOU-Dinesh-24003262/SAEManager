<?php
namespace Views\User;

use Views\AbstractView;
use Utilis\SessionService;

class RegisterView extends AbstractView 
{
    // Constantes pour les champs du formulaire
    public const FIELD_ID = 'id';
    public const FIELD_FNAME = 'fname';
    public const FIELD_LNAME = 'lname';
    public const FIELD_GENDER = 'gender';
    public const FIELD_USER_TYPE = 'user_type';
    public const FIELD_EMAIL = 'email';
    public const FIELD_PASSWORD = 'pwd';
    public const FIELD_PASSWORD_CONFIRM = 'pwdverif';
    public const FIELD_PHONE = 'tel';
    public const FIELD_DOB = 'dob';
    public const FIELD_CITY = 'city';
    public const FIELD_YEAR = 'year';
    public const FIELD_PARCOURS = 'parcours';
    public const FIELD_TD = 'td';
    public const FIELD_TP = 'tp';
    public const FIELD_TERMS = 'terms';

    private const TEMPLATE_HTML = __DIR__ . '/register.html';

    public function __construct()
    {
        $data = [
            'errors' => SessionService::getFlash('errors', []),
            'old_data' => SessionService::getFlash('old_data', []),
            'success' => SessionService::getFlash('success', '')
        ];
        parent::__construct($data);
    }

    protected function templatePath(): string 
    {
        return self::TEMPLATE_HTML;
    }

    protected function templateKeys(): array 
    {
        $oldData = $this->data['old_data'];
        $errors = $this->data['errors'];
        
        return [
            // Messages d'erreur
            'ERROR_MESSAGES' => $this->renderErrorMessages($errors),
            'SUCCESS_MESSAGE' => $this->renderSuccessMessage(),
            
            // Valeurs des champs
            'VALUE_ID' => $this-> $oldData[self::FIELD_ID] ?? '',
            'VALUE_FNAME' => $this-> $oldData[self::FIELD_FNAME] ?? '',
            'VALUE_LNAME' => $this-> $oldData[self::FIELD_LNAME] ?? '',
            'VALUE_EMAIL' => $this-> $oldData[self::FIELD_EMAIL] ?? '',
            'VALUE_PHONE' => $this-> $oldData[self::FIELD_PHONE] ?? '',
            'VALUE_DOB' => $this-> $oldData[self::FIELD_DOB] ?? '',
            'VALUE_CITY' => $this-> $oldData[self::FIELD_CITY] ?? '',
            
            // Sélections pour les radios
            'CHECKED_MALE' => $this->isChecked(self::FIELD_GENDER, 'male'),
            'CHECKED_FEMALE' => $this->isChecked(self::FIELD_GENDER, 'female'),
            'CHECKED_OTHER' => $this->isChecked(self::FIELD_GENDER, 'other'),
            
            // Sélections pour les selects
            'SELECTED_STUDENT' => $this->isSelected(self::FIELD_USER_TYPE, 'student'),
            'SELECTED_PROFESSOR' => $this->isSelected(self::FIELD_USER_TYPE, 'professor'),
            'SELECTED_COMPANY' => $this->isSelected(self::FIELD_USER_TYPE, 'companies'),
            
            'SELECTED_YEAR_1' => $this->isSelected(self::FIELD_YEAR, '1'),
            'SELECTED_YEAR_2' => $this->isSelected(self::FIELD_YEAR, '2'),
            'SELECTED_YEAR_3' => $this->isSelected(self::FIELD_YEAR, '3'),
            
            'SELECTED_PARCOURS_A' => $this->isSelected(self::FIELD_PARCOURS, 'A'),
            'SELECTED_PARCOURS_B' => $this->isSelected(self::FIELD_PARCOURS, 'B'),
            
            'SELECTED_TD1' => $this->isSelected(self::FIELD_TD, 'TD1'),
            'SELECTED_TD2' => $this->isSelected(self::FIELD_TD, 'TD2'),
            'SELECTED_TD3' => $this->isSelected(self::FIELD_TD, 'TD3'),
            'SELECTED_TD4' => $this->isSelected(self::FIELD_TD, 'TD4'),
            
            'SELECTED_TPA' => $this->isSelected(self::FIELD_TP, 'TPA'),
            'SELECTED_TPB' => $this->isSelected(self::FIELD_TP, 'TPB'),
            
            // Date maximale pour la date de naissance
            'MAX_BIRTH_DATE' => date('Y-m-d', strtotime('-16 years'))
        ];
    }

    private function renderErrorMessages(array $errors): string
    {
        if (empty($errors)) {
            return '';
        }

        $html = '<div class="alert alert-error"><ul>';
        foreach ($errors as $error) {
            $html .= '<li>' . $this-> $error . '</li>';
        }
        $html .= '</ul></div>';
        
        return $html;
    }

    private function renderSuccessMessage(): string
    {
        $success = $this->data['success'];
        if (empty($success)) {
            return '';
        }
        
        return '<div class="alert alert-success">' . $this-> $success . '</div>';
    }

    protected function getPageTitle(): string
    {
        return 'Inscription - SAEManager';
    }

    protected function getAdditionalScripts(): string
    {
        return '<script src="/scripts/register.js"></script>';
    }
}