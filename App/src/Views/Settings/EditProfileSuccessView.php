<?php

namespace Views\Settings;

use Core\AbstractView;

class EditProfileSuccessView extends AbstractView
{

    private const TEMPLATE_HTML = __DIR__ . '/edit-profile-success.html';

    /**
     * @return string
     */
    protected function templatePath(): string
    {
        return self::TEMPLATE_HTML;
    }

    /**
     * @return array
     */
    protected function templateKeys(): array
    {

        return [
            'FIRSTNAME' => $this->data['firstname'],
            'LASTNAME' => $this->data['lastname'],
            'PHONE' => $this->data['phone'],
        ];
    }

    /**
     * @return string
     */
    protected function getNameCss(): string
    {
        return 'profile.css';
    }
}