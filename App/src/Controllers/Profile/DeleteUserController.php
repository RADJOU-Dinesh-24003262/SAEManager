<?php

namespace Controllers\Profile;

use Core\ControllerInterface;
use Core\Utilis\SessionService;
use Models\User\User;
use PDOException;
use Views\Profile\DeleteUserView;

/**
 * DeleteUser Controller
 *
 * Handle User deletion (GET request)
 *
 * @category Controller
 *
 * @package Src
 *
 * @subpackage Controllers\Profile
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
class DeleteUserController implements ControllerInterface
{
    /**
     *  Main Controller logic for DeleterUser.
     *
     * @return void
     * @throws \PDOException If there is a problem with database request.
     */
    public function control(): void
    {

        if (!(SessionService::has('user_id'))) {
            http_response_code(404);
            echo "Page non trouvée";
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
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage());
        }
    }

    /**
     * Check if the controller should handle the current request
     *
     * @param  string $path   The request path.
     * @param  string $method The HTTP request method.
     * @return boolean True if path is /delete-user and the method is GET.
     */
    public static function support(string $path, string $method): bool
    {
        return $path === '/delete-user' && $method === 'GET';
    }
}
