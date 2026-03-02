<?php

namespace Controllers\Settings;

use Controllers\BaseController;
use Core\Utilis\SessionService;
use Models\Entity\User\User;
use Models\Repository\User\PdoUserRepository;
use Models\UseCase\User\DeleteUserUseCase;
use Override;
use PDOException;
use Views\Settings\DeleteUserView;

/**
 * DeleteUser Controller
 *
 * Handle User deletion (GET request)
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
class DeleteUserController extends BaseController
{
    /**
     *  Main Controller logic for DeleterUser.
     *
     * @return void
     * @throws PDOException If there is a problem with database request.
     */
    public function control(): void
    {
        $this->ensureAuthenticated();

        $data['user'] = $this->user;

        try {
            $email = $this->user->getEmail();

            $userRepository = new PdoUserRepository();
            $deleteUserUseCase = new DeleteUserUseCase($userRepository);
            $deleteUserUseCase->executeByEmail($email);

            $view = new DeleteUserView($data);
            $view->render();
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage());
        }
    }

    /**
     * Check if the controller should handle the current request
     *
     * @param  string $path   The request path.
     * @param  string $method The HTTP request method.
     * @return boolean True if path is /settings/delete and the method is GET.
     */
    #[Override]
    public static function support(string $path, string $method): bool
    {
        return $path === '/settings/delete' && $method === 'GET';
    }
}
