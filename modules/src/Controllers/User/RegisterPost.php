<?php
namespace Controllers\User;

use Controllers\ControllerInterface;
use Models\User\User;
use Utilis\ValidationService;
use Utilis\SessionService;
use Views\User\RegisterView;
use Views\User\RegisterSuccessView;

class RegisterPost implements ControllerInterface
{
    public function control(): void
    {
        // Validation des données
        $validator = new ValidationService();
        $data = $validator->escape($_POST);
        
        
        try {
            $validator->validateRegistrationData($data);
            if (!empty($errors)) {
                // Affichage du formulaire avec les erreurs
                throw new ExeptionValidationRegisters($errors);
                //SessionService::setFlash('errors', $errors);
                //SessionService::setFlash('old_data', $_POST);
                $view = new RegisterView();
                $view->render();
                return;
            }

            // Création de l'utilisateur
            $user = User::createFromRegistrationData($_POST);
            
            // Sauvegarde en base (à implémenter)
            if ($user->save()) {
                SessionService::setFlash('success', 'Inscription réussie !');
                $view = new RegisterSuccessView($user);
                $view->render();
            } else {
                throw new \Exception("Erreur lors de la sauvegarde");
            }
            
        } catch (\Exception $e) {
            SessionService::setFlash('errors', ['general' => 'Erreur lors de l\'inscription: ' . $e->getMessage()]);
            SessionService::setFlash('old_data', $_POST);
            $view = new RegisterView();
            $view->render();
        }
    }

    public static function support(string $chemin, string $method): bool
    {
        return $chemin === "/register" && $method === "POST";
    }
}