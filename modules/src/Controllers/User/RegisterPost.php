<?php

namespace Controllers\User;

use Controllers\ControllerInterface;
use includes\exception\ExceptionValidationEmptys;
use Models\User\User;
use Utilis\Validator\ValidationServiceRegister;
use Utilis\SessionService;
use Views\User\RegisterView;
use Views\User\RegisterSuccessView;
use includes\exception\ExceptionValidationRegisters;

/**
 * Class RegisterPost

 * This class controls the register process (post).

 * @category    Controller

 * @package     Src

 * @subpackage  Controllers\User

 * @author      Alexandre Benhafessa <alexandre.benhafessa@etu.univ-amu.fr>,
 *              François Dargentolle <francois.dargentolle@etu.univ-amu.fr>,
 *              William Edelstein <william.edelstein@etu.univ-amu.fr>,
 *              Nathan Griguer <nathan.griguer@etu.univ-amu.fr>,
 *              Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>

 * @license     MIT License https://opensource.org/licenses/MIT

 * @link        https://github.com/RADJOU-Dinesh-24003262/SAEManager

 */
class RegisterPost implements ControllerInterface
{
    /**
     * Principal manager of the controller
     *
     * @return void
     *
     * @throws \Exception For any other unexpected errors during the registration process.
     */
    public function control(): void
    {
        // Validate the data.
        $validator = new ValidationServiceRegister();

        try {
            $data = $validator->escape($_POST);
            $validator->validate($data);

            // Create the user.
            $user = User::createFromRegistrationData($data);

            // Save the user HAS TEMPORARILY BEEN CHANGED, OLD CODE IN COMMENT
            /*
            if ($user->save()) {
                error_log("Nouvel utilisateur enregistré: " . $user->getEmail());
                $view = new RegisterSuccessView($user);
                $view->render();

                return;
            } else {
                error_log("Erreur sauvegarde utilisateur: " . $user->getEmail());
                throw new \Exception("Erreur lors de la sauvegarde");
            }
                */
            $user->save();
            error_log("Nouvel utilisateur enregistré: " . $user->getEmail());
            $view = new RegisterSuccessView($user);
            $view->render();


        } catch (ExceptionValidationRegisters | ExceptionValidationEmptys $e) {
            $errors = [];
            foreach ($e->getErrors() as $error) {
                $errors[] = $error->getMessage();
            }
            SessionService::setFlash('errors', $errors);
        } catch (\PDOException $e) {
            error_log("Erreur récupération données utilisateur: " . $e->getMessage());
            SessionService::setFlash('errors', ['general' => 'Une eurreur est survenu, réessayez plus tard']);
        } catch (\Exception $e) {
            SessionService::setFlash('errors', ['general' => 'Erreur lors de l\'inscription: ' . $e->getMessage()]);
        }
        $view = new RegisterView();
        $view->render();
    }

    /**
     * Determines whether this controller supports the given request.
     *
     * @param string $path   The requested URI path.
     * @param string $method The HTTP method used in the request.
     *
     * @return boolean True if the path is "/register" and the method is POST.
     */
    public static function support(string $path, string $method): bool
    {
        return $path === "/register" && $method === "POST";
    }
}