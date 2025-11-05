<?php

namespace Views\Profile;

use Core\AbstractView;

class EditProfileView extends AbstractView
{
    /**
     * Path to the HTML template file used for rendering the profile page.
     *
     * @var string
     */
    private const TEMPLATE_HTML = __DIR__ . '/edit-profile.html';

    /**
     * Returns the path to the HTML template file.
     *
     * @return string The full path to the template file.
     */
    protected function templatePath(): string
    {
        return self::TEMPLATE_HTML;
    }


    /**
     * Returns the list of keys and rendered values used in the HTML template.
     *
     * @return array The list of template keys and values.
     */
    protected function templateKeys(): array
    {
        $user = $this->data['user'];

        return [
            'FIRSTNAME' => $user->getFirstName(),
            'LASTNAME' => $user->getLastName(),
            'PHONE' => $user->getPhone(),
        ];
    }

    /**
     * Returns the name of the CSS file associated with this view.
     *
     * @return string The CSS filename.
     */
    protected function getNameCss(): string
    {
        return 'profile.css';
    }
}
