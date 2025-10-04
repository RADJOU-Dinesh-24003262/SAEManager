<?php

namespace Views\User;

use Views\AbstractView;

class LoginView extends AbstractView
{
    private const TEMPLATE_HTML = __DIR__ . '/loginview.html';

    protected function templatePath(): string
    {
        return self::TEMPLATE_HTML;
    }

    protected function templateKeys(): array
    {
        return [];
    }

    protected function getNameCss(): string
    {
        return 'login.css';
    }
}