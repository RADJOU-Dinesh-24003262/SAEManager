<?php

namespace Controllers\Profile;

use Core;
use Core\ControllerInterface;
use Core\includes\exception\ExceptionDashboard;
use Core\Utilis\SessionService;
use Views\Dashboard\DashboardView;
use Core\includes\exception;
use Views\Profile\ProfileView;

class ProfileController implements ControllerInterface
{
    public function control(): void
    {

        //Temporary Solution
        if (!(SessionService::has('user_id'))) {
            header('Location: /');
            exit();
        }

        $user = unserialize(SessionService::get('USER'));
        $data['user'] = $user;
        $view = new ProfileView($data);
        $view->render();
    }

    public static function support(string $path, string $method): bool
    {
        return $path === '/profile' and $method === 'GET';
    }
}
