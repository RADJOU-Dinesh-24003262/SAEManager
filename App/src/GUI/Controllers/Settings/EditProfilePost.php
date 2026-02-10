<?php

namespace App\GUI\Controllers\Settings;

use Core\Controllers\ControllerInterface;
 use App\Infrastructure\Service\SessionService;
 use App\Application\User\UpdateUserProfileUseCase;
 use App\Infrastructure\Persistence\Pdo\PdoUserRepository;
 use Override;
 use PDOException;
 use \App\Application\Validation\User\EditProfileValidator;
 use App\GUI\Views\Settings\EditProfileSuccessView;
use App\Infrastructure\Security\InputSanitizer;

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
class EditProfilePost implements ControllerInterface
{
    /**
     * Main Controller logic for EditProfilePost.
     *
     * @return void
     * @throws PDOException Trigger PDOException when BD is not accessible.
     */
    #[Override]
    public function control(): void
    {
        if (!SessionService::get('USER')) {
            header('Location: /login');
            exit;
        }
        $user = unserialize(SessionService::get('USER'));
        $validator = new EditProfileValidator();

        // Sanitization now done before validation
            $data = InputSanitizer::sanitize($_POST);
        $validator->validate($data);
        $email = $user->getEmail();

        $useCase = new UpdateUserProfileUseCase(new PdoUserRepository());
        $updatedUser = $useCase->execute($email, $data['phone']);

        if ($updatedUser) {
             SessionService::set('USER', serialize($updatedUser));
        }

        $view = new EditProfileSuccessView($data);
        $view->render();
    }

    /**
     * Check if the controller should handle the current request
     *
     * @param  string $path   The request path.
     * @param  string $method The HTTP request method.
     * @return boolean True if path is /edit-profile and the method is POST.
     */
    #[Override]
    public static function support(string $path, string $method): bool
    {
        return $path === '/edit-profile' && $method === 'POST';
    }
}
