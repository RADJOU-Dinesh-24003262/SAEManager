<?php

namespace Controllers\PageSae;

use Core\ControllerInterface;
use Core\includes\Database;
use Core\Utilis\SessionService;
use Views\PageSAE\PageSaeView;
use PDO;

class PageSaeController implements ControllerInterface
{
    public function control(): void
    {
        if (!SessionService::has('user_id')) {
            header('Location: /');
            exit();
        }

        // Normaliser le chemin sans querystring
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Route dynamique : /sae/{id}
        if (preg_match('#^/sae/(\d+)$#', $uri, $m)) {
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

                // Préparer les valeurs à passer à la vue (adapter selon colonnes réelles)
                $data = [
                    'sae_subject_id' => $row['sae_subject_id'],
                    'subject_name' => $row['subject_name'] ?? '',
                    'begin_date' => $row['begin_date'] ?? '',
                    'end_date' => $row['end_date'] ?? '',
                    'description' => $row['description'] ?? '',
                    'rendu' => $row['rendu'] ?? '',
                    // si compétences stockées en JSON : json_decode($row['competences'], true)
                    'competences' => isset($row['competences']) ? json_decode($row['competences'], true) : []
                ];

                $view = new PageSaeView($data);
                $view->render();
                return;
            } catch (\Exception $e) {
                error_log("PageSaeController error fetching SAE: " . $e->getMessage());
                header('Location: /error');
                exit();
            }
        }

        // ancienne route statique /page-sae (sans id)
        if ($uri === '/page-sae') {
            $view = new PageSaeView();
            $view->render();
            return;
        }

        header('Location: /');
        exit();
    }

    public static function support(string $path, string $method): bool
    {
        $method = strtoupper($method);
        if ($method !== 'GET') {
            return false;
        }
        if ($path === '/page-sae') {
            return true;
        }
        return (bool) preg_match('#^/sae/\d+$#', $path);
    }
}
