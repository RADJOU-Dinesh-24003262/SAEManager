<?php

namespace Controllers\Settings;

use Validator\EditProfileValidator;
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

        $user = unserialize(SessionService::get('USER'));
        $validator =  new EditProfileValidator();

        try {
            $data = $validator->escape($_POST);
            $validator->validate($data);
            $email = $user->getEmail();
            if (!empty($data['phone'])) {
                User::modifyField('phone', $data['phone'], $email);
            }

            $user->fetchData($email);

            SessionService::set('USER', serialize($user));

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
