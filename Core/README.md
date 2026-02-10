# Core - Bibliothèque Réutilisable PHP

Bibliothèque de composants génériques PHP réutilisables pour applications web.

## 🎯 Objectif

Cette bibliothèque contient des composants génériques, indépendants de toute logique métier spécifique, conçus pour être réutilisables dans n'importe quel projet PHP.

---

## 📦 Composants

### Controllers

**`Core\Controllers\ControllerInterface`**

Interface pour le pattern Controller dans une architecture MVC.

```php
interface ControllerInterface
{
    public function control(): void;
    public static function support(string $path, string $method): bool;
}
```

**Usage:**
```php
use Core\Controllers\ControllerInterface;

class MyController implements ControllerInterface
{
    public function control(): void
    {
        // Logique du controller
    }
    
    public static function support(string $path, string $method): bool
    {
        return $path === '/my-route' && $method === 'GET';
    }
}
```

---

### Database

**`Core\Database\DatabaseConnection`**

Singleton PDO pour connexion base de données.  
Configuration via fichier INI.

**Configuration INI attendue:**
```ini
[database]
driver = "mysql"         ; ou "pgsql"
host = "localhost"
port = 3306              ; optionnel
schema = "my_database"
username = "user"
password = "pass"
```

**Usage:**
```php
use Core\Database\DatabaseConnection;

$pdo = DatabaseConnection::getInstance('config/database.ini');
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
```

---

### Views

**`Core\Views\AbstractView`**

Classe abstraite pour le rendering de vues.

**Usage:**
```php
use Core\Views\AbstractView;

class MyView extends AbstractView
{
    protected function templatePath(): string
    {
        return __DIR__ . '/templates/my_view.php';
    }
    
    public function render(): void
    {
        // Logique de rendering
        include $this->templatePath();
    }
}
```

---

### Models

**`Core\Models\BaseEntity`**

Classe de base pour les entités avec hydratation et sérialisation automatiques via Reflection.

**Usage:**
```php
use Core\Models\BaseEntity;

class User extends BaseEntity
{
    private int $id;
    private string $name;
    private string $email;
    
    // Getters/Setters...
}

// Hydratation depuis array
$user = new User();
$user->hydrate(['name' => 'John', 'email' => 'john@example.com']);

// Sérialisation vers array
$data = $user->toArray();
```

---

## 🚀 Installation

### Dans un nouveau projet

1. **Copier la bibliothèque:**
```bash
cp -r /path/to/SAEManager/Core /path/to/nouveau-projet/Core
```

2. **Configurer l'autoloading Composer:**

Ajouter dans `composer.json`:
```json
{
    "autoload": {
        "psr-4": {
            "Core\\": "Core/"
        }
    }
}
```

3. **Régénérer l'autoloader:**
```bash
composer dump-autoload
```

---

## 📖 Documentation

### DatabaseConnection

**Méthodes principales:**
- `getInstance(?string $configFile = null): PDO` - Obtient l'instance singleton
- `setInstance(PDO $pdo): void` - Définit une instance personnalisée (pour tests)
- `reset(): void` - Réinitialise le singleton (pour tests)

**Avantages:**
- ✅ Singleton: une seule connexion par application
- ✅ Configuration centralisée via INI
- ✅ Support MySQL et PostgreSQL
- ✅ Testable (setInstance pour mock)

---

### BaseEntity

**Méthodes principales:**
- `hydrate(array $data): self` - Remplit l'objet depuis un array
- `toArray(): array` - Convertit l'objet en array

**Fonctionnement:**
- Utilise la Reflection PHP pour l'hydratation automatique
- Supporte les propriétés privées/protégées
- Gère les types (int, string, bool, DateTime, etc.)

---

## 🔧 Utilisation Avancée

### Injection de Dépendances avec DatabaseConnection

```php
class UserRepository
{
    private PDO $pdo;
    
    public function __construct()
    {
        $this->pdo = DatabaseConnection::getInstance();
    }
    
    public function findById(int $id): ?User
    {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $data ? (new User())->hydrate($data) : null;
    }
}
```

---

## ✅ Avantages

### Pour le Projet Original (SAEManager)
- ✅ Séparation code générique / code métier
- ✅ Architecture Clean respectée
- ✅ Maintenabilité améliorée
- ✅ Réduction de la dette technique

### Pour Futurs Projets
- ✅ Composants éprouvés et testés
- ✅ Gain de temps (pas besoin de recoder Controller, Database, etc.)
- ✅ Pattern cohérents out-of-the-box
- ✅ Prêt pour PSR-4

---

## 📝 Versions

### v2.0.0 (Actuelle)
- ✅ Database refactorisé (DatabaseConnection générique)
- ✅ BaseModel renommé en BaseEntity
- ✅ Exceptions déplacées vers App/
- ✅ Services déplacés vers App/Infrastructure
- ✅ Documentation complète

### v1.0.0 (Legacy - deprecated)
- ⚠️ Database monolithique (429 lignes)
- ⚠️ Mélange code générique/spécifique

---

## 🤝 Contribution

Cette bibliothèque fait partie du projet SAEManager.  
Pour contribuer: [github.com/RADJOU-Dinesh-24003262/SAEManager](https://github.com/RADJOU-Dinesh-24003262/SAEManager)

---

## 📄 License

MIT License - Voir fichier LICENSE

---

## 👥 Auteurs

- Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
- Alexandre Benhafessa
- François Dargentolle
- William Edelstein
- Nathan Griguer

---

**⚡ Core/ - Simple, Générique, Réutilisable**
