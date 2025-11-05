<?php

namespace Controllers\Profile;

use Core\ControllerInterface;
use Views\Profile\EditProfileSuccessView;

class EditProfilePost implements ControllerInterface
{
    /**
     * @return void
     */
    public function control(): void
    {
        $data = $_POST;
        var_dump($data);
        $view = new EditProfileSuccessView($data);
        $view->render();
    }

    /**
     * @param string $path
     * @param string $method
     * @return boolean
     */
    public static function support(string $path, string $method): bool
    {
        return $path === '/send-edit-profile' && $method === 'POST';
    }
}
