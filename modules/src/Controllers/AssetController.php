<?php
namespace Controllers;

class AssetController implements ControllerInterface
{
    private string $chemin;

    public function __construct()
    {
        $this->chemin = $_SERVER['REQUEST_URI'];
    }

    public function control(): void
    {
        $filePath = __DIR__ . '/../../../_assets' . $this->chemin;

        if (!file_exists($filePath) || !is_file($filePath)) {
            http_response_code(404);
            echo "Fichier non trouvé.";
            return;
        }

        $extension = pathinfo($filePath, PATHINFO_EXTENSION);

        switch ($extension) {
            case 'css':
                header('Content-Type: text/css');
                break;
            case 'js':
                header('Content-Type: application/javascript');
                break;
            default:
                http_response_code(415);
                echo "Type de fichier non supporté.";
                return;
        }

        readfile($filePath);
    }

    public static function support(string $chemin, string $method): bool
    {
        return $method === 'GET' && preg_match('/\.(css|js)$/', $chemin);
    }
}
