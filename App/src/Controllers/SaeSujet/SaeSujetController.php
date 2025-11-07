<?php

namespace Controllers\SaeSujet;

use Core\ControllerInterface;
use Core\includes\Database;
use Core\Utilis\SessionService;
use Models\SAE\SAE;
use Validator\SaeSujetValidator;
use Views\SaeSujet\SaeSujetView;
use Models\User\User;
use Models\User\Professor;
use PDO;

/**
 * Controller for the form of the subject of the SAE.
 */
class SaeSujetController implements ControllerInterface
{
    public function control(): void
    {
        $user = unserialize(SessionService::get('USER'));

        if (!SessionService::has('user_id')) {
            header('Location: /');
            exit();
        }

        if (!($user->isProfessor())) {
            header('Location: /');
            exit();
        }

        // Normaliser le chemin sans querystring
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Route pour création d'une nouvelle SAE
        if ($uri === '/create') {
            $view = new SaeSujetView();
            $view->render();
            return;
        }

        // Route d'édition: /sae/{id}modify  (ex: /sae/123modify)
        if (preg_match('#^/sae/(\d+)modify$#', $uri, $m)) {
            $saeId = (int) $m[1];
            try {
                $connection = Database::getInstance();
                $stmt = $connection->prepare('SELECT * FROM sae_subjects WHERE sae_subject_id = :id');
                $stmt->execute(['id' => $saeId]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$row) {
                    header('Location: /dashboard');
                    exit();
                }

                // Préparer les valeurs à injecter dans la vue (noms correspondant aux inputs)
                $data = [
                    'sae_subject_id' => $row['sae_subject_id'],
                    'nameSae' => $row['subject_name'] ?? '',
                    'date_rendu' => $row['end_date'] ?? '',
                    // colonnes additionnelles non présentes dans la table sae_subjects
                    // description, rendu (url) et compétences seront vides si non stockées ailleurs
                    'decrip_sujet' => $row['description'] ?? '',
                    'rendu' => $row['rendu'] ?? '',
                    // si vous stockez compétences sous forme JSON, vous pouvez décoder ici :
                    // 'competence' => isset($row['competences']) ? json_decode($row['competences'], true) : []
                ];

                $view = new SaeSujetView($data);
                $view->render();
                return;
            } catch (\Exception $e) {
                error_log("SaeSujetController error fetching SAE: " . $e->getMessage());
                header('Location: /error');
                exit();
            }
        }

        // POST traitement (création / mise à jour) existant
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $connection = Database::getInstance();
                $validator = new SaeSujetValidator();
                $data = $validator->escape($_POST);
                $validator->validate($data);

                // Ici ajouter la logique de création / mise à jour en fonction de la présence de sae_subject_id
                // ...
                header('Location: /dashboard');
                exit();
            } catch (\Exception $e) {
                error_log("Error in SaeSujetController POST: " . $e->getMessage());
                header('Location: /error');
                exit();
            }
        }

        // Par défaut rediriger
        header('Location: /');
        exit();
    }

    public static function support(string $path, string $method): bool
    {
        return $path === "/create" && $method === "POST";
    }
}
