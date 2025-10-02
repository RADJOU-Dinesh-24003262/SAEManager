<?php
namespace Controllers\User;

use Controllers\ControllerInterface;
use Models\User\User;
use Utilis\ValidationServiceRegister;
use Utilis\SessionService;
use Views\User\RegisterView;
use Views\User\RegisterSuccessView;
use includes\exception\ExceptionValidationRegisters;

class RegisterPost implements ControllerInterface
{
    public function control(): void
    {
        // Validation des données
        $validator = new ValidationServiceRegister();        
        
        try {
            $data = $validator->escape($_POST);
            $validator->validateRegistrationData($data);

            // Création de l'utilisateur
            $user = User::createFromRegistrationData($data);
            
            // Sauvegarde en base (à implémenter)
            if ($user->save()) {
                SessionService::setFlash('success', 'Inscription réussie !');
                $view = new RegisterSuccessView($user);
                $view->render();
            } else {
                throw new \Exception("Erreur lors de la sauvegarde");
            }
            
        }catch (ExceptionValidationRegisters $e) {
            $errors = [];
            foreach ($e->getErrors() as $error) {
                $errors[] = $error->getMessage();
            }
            SessionService::setFlash('errors', $errors);


            $view = new RegisterView();
            $view->render();
        } catch (\Exception $e) {
            SessionService::setFlash('errors', ['general' => 'Erreur lors de l\'inscription: ' . $e->getMessage()]);
            $view = new RegisterView();
            $view->render();
        }
    }

    public static function support(string $chemin, string $method): bool
    {
        return $chemin === "/register" && $method === "POST";
    }
}