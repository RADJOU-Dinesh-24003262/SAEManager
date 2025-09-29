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
        ];
        parent::__construct($data);
    }

    protected function templatePath(): string 
    {
        return self::TEMPLATE_HTML;
    }

    protected function templateKeys(): array 
    {
        $errors = $this->data['errors'];
        
        return [
            // Messages d'erreur
            'ERROR_MESSAGES' => $this->renderErrorMessages($errors),
            
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
            $html .= '<li>' . $error . '</li>';
        }
        $html .= '</ul></div>';
        
        return $html;
    }


    protected function getPageTitle(): string
    {
        return 'Inscription - SAEManager';
    }

    protected function getAdditionalScripts(): string
    {
        return '<script src="/scripts/register.js"></script>';
    }

    protected function getNameCss(): string
    {
        return 'register.css';
    }
}