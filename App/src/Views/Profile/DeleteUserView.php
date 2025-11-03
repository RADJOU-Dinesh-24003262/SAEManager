<?php

namespace Views\Profile;

use Core\AbstractView;

class DeleteUserView extends AbstractView
{
    private const TEMPLATE_HTML = __DIR__ . '/delete-user-view.html';

    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }

    public function templateKeys(): array
    {
        $user = $this->data['user'];

        return [
            'EMAIL' => $user->getEmail()
        ];
    }

    public function templatePath(): string
    {
        return self::TEMPLATE_HTML;
    }

    public function getNameCss(): string
    {
        return "";
    }
}
