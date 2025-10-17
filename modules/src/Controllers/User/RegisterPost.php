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
 * Class User
 
 * @package     src

 * @subpackage  Controllers\User

 * @author      Benhafessa Alexandre, Dargentolle Francois, Edelstein William, Griguer Nathan, Radjou Dinesh

 * This class controls the register process (post).
 */
class RegisterPost implements ControllerInterface
{
    /**
     * Principal manager of the controller
     * 
     * @return void
     */
    public function control(): void
    {
        // Validate the data
        $validator = new ValidationServiceRegister();        
        
        try {
            $data = $validator->escape($_POST);
            $validator->validate($data);

            // Create the user
            $user = User::createFromRegistrationData($data);
            
            // Save the user
            if ($user->save()) {
                error_log("Nouvel utilisateur enregistré: " . $user->getEmail());
                SessionService::set('user_id', $user->getEmail());
                $view = new RegisterSuccessView($user);
                $view->render();

                return;
            } else {
                error_log("Erreur sauvegarde utilisateur: " . $user->getEmail());
                throw new \Exception("Erreur lors de la sauvegarde");
            }
            
        
        }catch (ExceptionValidationRegisters | ExceptionValidationEmptys $e) {
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
     * Check if this controller can handle the request
     * 
     * @return boolean Is the method post?
     */
    public static function support(string $chemin, string $method): bool
    {
        return $chemin === "/register" && $method === "POST";
    }
}