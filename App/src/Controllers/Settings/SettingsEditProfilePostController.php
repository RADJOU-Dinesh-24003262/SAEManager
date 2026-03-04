<?php

namespace Controllers\Settings;

use Controllers\BaseController;
use Core\Utilis\SessionService;
use Models\Entity\User\User;
use Models\Repository\User\PdoUserRepository;
use Models\UseCase\User\UpdateProfileUseCase;
use Override;
use PDOException;
use Validator\EditProfileValidator;
use Views\Settings\EditProfileSuccessView;

/**
 * Edit Profile Post Controller
 *
 * Handle User Profile Update (POST request)
 *
 * @category Controller
 *
 * @package Src
 *
 * @subpackage Controllers/Settings
 *
 * @author Alexandre Benhafessa <alexandre.benhafessa@etu.univ-amu.fr>
 * @author François Dargentolle <francois.dargentolle@etu.univ-amu.fr>
 * @author William Edelstein <william.edelstein@etu.univ-amu.fr>
 * @author Nathan Griguer <nathan.griguer@etu.univ-amu.fr>
 * @author Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 *
 * @license MIT License https://opensource.org/licenses/MIT
 *
 * @link https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class SettingsEditProfilePostController extends BaseController
{
    /**
     * Main Controller logic for EditProfilePost.
     *
     * @return void
     * @throws PDOException Trigger PDOException when BD is not accessible.
     */
    public function control(): void
    {

        $this->ensureAuthenticated();
        $validator = new EditProfileValidator();

        $data = $validator->escape($_POST);
        $validator->validate($data);

        $userRepository = new PdoUserRepository();
        $updateProfileUseCase = new UpdateProfileUseCase($userRepository);
        $user = $updateProfileUseCase->execute($this->user->getUserId(), $data);


        SessionService::set('USER', serialize($user));

        $view = new EditProfileSuccessView($data);
        $view->render();
    }

    /**
     * Check if the controller should handle the current request
     *
     * @param  string $path   The request path.
     * @param  string $method The HTTP request method.
     * @return boolean True if path is /settings/edit-profile and the method is POST.
     */
    #[Override]
    public static function support(string $path, string $method): bool
    {
        return $path === '/settings/edit-profile' && $method === 'POST';
    }
}
