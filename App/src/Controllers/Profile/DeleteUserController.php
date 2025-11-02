<?php

namespace Controllers\Profile;

use Core\ControllerInterface;
use Core\Utilis\SessionService;
use Models\User\User;
use Views\Profile\DeleteUserView;

class DeleteUserController implements ControllerInterface
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

        try {
            $email = $user->getEmail();
            User::deleteByEmail($email);
            $view = new DeleteUserView($data);

            // Clear session.
            session_unset();     // Unset all session variables.
            session_destroy();   // Destroy the session.

            $view->render();

        } catch (\PDOException $e) {
            throw new \PDOException($e->getMessage());
        }
    }

    public static function support($path, $method): bool
    {
        return $path === '/delete-user' && $method === 'GET';
    }
}
