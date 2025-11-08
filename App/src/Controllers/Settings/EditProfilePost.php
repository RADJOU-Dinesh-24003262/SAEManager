<?php

namespace Controllers\Settings;

use Validator\EditProfileValidator;
use Core\ControllerInterface;
use Core\Utilis\SessionService;
use Models\User\User;
use Views\Settings\EditProfileSuccessView;

/**
 * Edit Profile Post Controller
 *
 * Handle User Profile Update (POST request)
 * @category Controller
 *
 * @package Src
 *
 * @subpackage Controllers\Settings
 *
 * @author  Alexandre Benhafessa <alexandre.benhafessa@etu.univ-amu.fr>
 * @author  François Dargentolle <francois.dargentolle@etu.univ-amu.fr>
 * @author  William Edelstein <william.edelstein@etu.univ-amu.fr>
 * @author  Nathan Griguer <nathan.griguer@etu.univ-amu.fr>
 * @author  Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 *
 * @license MIT License https://opensource.org/licenses/MIT
 *
 * @link https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class EditProfilePost implements ControllerInterface
{
    /**
     * Main Controller logic for EditProfilePost.
     *
     * @return void
     * @throws \PDOException Trigger PDOException when BD is not accessable.
     */
    public function control(): void
    {
        $user = unserialize(SessionService::get('USER'));
        $validator = new EditProfileValidator();

        $data = $validator->escape($_POST);
        $validator->validate($data);
        $email = $user->getEmail();
        User::modifyField('phone', $data['phone'], $email);

        $user->fetchData($email);

        SessionService::set('USER', serialize($user));

        $view = new EditProfileSuccessView($data);
        $view->render();
    }

    /**
     * Check if the controller should handle the current request
     *
     * @param string $path   The request path.
     * @param string $method The HTTP request method.
     * @return boolean True if path is /edit-profile and the method is POST.
     */
    public static function support(string $path, string $method): bool
    {
        return $path === '/edit-profile' && $method === 'POST';
    }
}
