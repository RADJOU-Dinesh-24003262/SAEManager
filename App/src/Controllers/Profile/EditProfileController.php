<?php

namespace Controllers\Profile;

use Core\ControllerInterface;
use Core\Utilis\SessionService;
use Views\Profile\EditProfileView;

class EditProfileController implements ControllerInterface
{
    public function control(): void
    {
        if (!(SessionService::has('user_id'))) {
            header('Location: /');
            exit();
        }

        $user = unserialize(SessionService::get('USER'));
        $data['user'] = $user;
        $view = new EditProfileView($data);
        $view->render();
    }

    public static function support(string $path, string $method): bool
    {
        return $path === '/edit-profile' && $method === 'GET';
    }
}
