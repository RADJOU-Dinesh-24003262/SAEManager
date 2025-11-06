<?php

namespace Controllers\Settings;

use Core\ControllerInterface;
use Core\Utilis\SessionService;
use Models\User\User;
use Views\Settings\EditProfileSuccessView;

class EditProfilePost implements ControllerInterface
{
    /**
     * @return void
     */
    public function control(): void
    {
        $data = $_POST;
        $user = unserialize(SessionService::get('USER'));

        $data['user'] = $user;

        try {
            $email = $user->getEmail();
            foreach ($user as $field => $value) {
                if (!($value === '')) {
                    User::modifyField($field, $value, $email);

                }
            }

            $view = new EditProfileSuccessView($data);
            $view->render();
        } catch (\PDOException $e) {
            throw new \PDOException($e->getMessage());
        }
    }

    /**
     * @param string $path
     * @param string $method
     * @return bool
     */
    public static function support(string $path, string $method): bool
    {
        return $path === '/send-edit-profile' && $method === 'POST';
    }
}
