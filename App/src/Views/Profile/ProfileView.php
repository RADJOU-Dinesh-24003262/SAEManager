<?php

namespace Views\Profile;

use Views\Dashboard\DashboardView;
use Core\AbstractView;

class ProfileView extends AbstractView
{
    /**
     * Path to the HTML template file used for rendering the profile page.
     *
     * @var string
     */
    private const TEMPLATE_HTML = __DIR__ . '/profile.html';


    public function __construct(array $data)
    {
        parent::__construct($data);
    }

    /**
     * Returns the path to the HTML template file.
     *
     * @return string The full path to the template file.
     */
    protected function templatePath(): string
    {
        return self::TEMPLATE_HTML;
    }



    protected function templateKeys(): array
    {
        $user = $this->data['user'];

        return [
            'FULLNAME' => $user->getFullName(),
        ];
    }

    protected function getNameCss(): string
    {
        return 'profile.css';
    }
}
