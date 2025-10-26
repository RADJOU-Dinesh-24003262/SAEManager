# 🚀 Installation des outils de qualité de code

## 📋 Prérequis

- PHP 8.1+
- Composer
- Git

## 🔧 Installation rapide

### 1. Installer les dépendances

```bash
composer install
```

Ou avec le Makefile:
```bash
make install
```

### 2. Installer les Git hooks

```bash
# Copier le hook pre-commit
cp .git/hooks/pre-commit.sample .git/hooks/pre-commit
chmod +x .git/hooks/pre-commit

# Ou simplement
make hooks
```

### 3. Vérifier l'installation

```bash
make quality
```

## 📁 Structure des fichiers

```
.
├── .github/
│   └── workflows/
│       └── php-quality.yml      # GitHub Actions
├── tests/
│   ├── Unit/                    # Tests unitaires
│   ├── Integration/             # Tests d'intégration
│   └── bootstrap.php            # Bootstrap PHPUnit
├── coverage-checker.php         # Vérificateur de couverture
├── phpunit.xml                  # Configuration PHPUnit
├── phpstan.neon                 # Configuration PHPStan
├── phpcs-phpdoc.xml            # Configuration PHPDoc
├── composer.json               # Dépendances
└── Makefile                    # Commandes utiles
```

## 🎯 Commandes disponibles

### Via Composer

```bash
# Tests
composer test                    # Lance tous les tests
composer test-coverage          # Génère le rapport de couverture

# Qualité de code
composer phpcs                  # Vérifie le style PSR-12
composer phpcbf                 # Corrige automatiquement
composer phpstan                # Analyse statique
composer quality                # Lance toutes les vérifications
```

### Via Makefile (recommandé)

```bash
make help                       # Liste toutes les commandes
make test                       # Tests avec affichage détaillé
make test-unit                  # Tests unitaires uniquement
make test-integration           # Tests d'intégration uniquement
make coverage                   # Rapport de couverture HTML
make phpcs                      # Vérification PSR-12
make phpstan                    # Analyse statique
make phpdoc                     # Vérification documentation
make fix                        # Correction automatique
make quality                    # Toutes les vérifications
make ci                        # Simule le pipeline CI
make clean                     # Nettoie les fichiers temporaires
```

## ✅ Vérifications effectuées

### 1. Pre-commit (local)

Avant chaque commit, le hook vérifie:
- ✅ Syntaxe PHP valide
- ✅ Respect du PSR-12
- ✅ Documentation PHPDoc
- ✅ Analyse statique PHPStan

### 2. GitHub Actions (CI/CD)

Sur chaque push/PR, le pipeline vérifie:
- ✅ Validation composer.json
- ✅ Code style PSR-12
- ✅ Analyse statique (niveau 5)
- ✅ Documentation complète
- ✅ Tests unitaires et d'intégration
- ✅ Couverture de code (minimum 70%)
- ✅ Audit de sécurité

## 📝 Écrire des tests

### Test unitaire

```php
// tests/Unit/MyClassTest.php
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * @covers \MyNamespace\MyClass
 */
class MyClassTest extends TestCase
{
    /**
     * @test
     * @covers \MyNamespace\MyClass::myMethod
     */
    public function it_should_do_something(): void
    {
        // Arrange
        $instance = new MyClass();
        
        // Act
        $result = $instance->myMethod();
        
        // Assert
        $this->assertEquals('expected', $result);
    }
}
```

### Test d'intégration

```php
// tests/Integration/UserRegistrationTest.php
namespace Tests\Integration;

use PHPUnit\Framework\TestCase;

/**
 * @group integration
 */
class UserRegistrationTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_register_a_new_user(): void
    {
        // Test du flux complet
    }
}
```

## 📊 Couverture de code

### Générer le rapport

```bash
make coverage
```

### Consulter le rapport

Le rapport HTML est disponible dans `coverage/index.html`

### Objectif

- **Minimum requis**: 70%
- **Recommandé**: 80%+

## 🔍 Correction des erreurs

### Erreurs de style

```bash
# Voir les erreurs
make phpcs

# Corriger automatiquement
make fix
```

### Erreurs PHPStan

```bash
# Analyser le code
make phpstan

# Ajouter des types manquants dans votre code
# Exemple: ajouter @param, @return, @var
```

### Documentation manquante

```bash
# Vérifier la documentation
make phpdoc

# Ajouter les PHPDoc manquants
/**
 * Description de la méthode
 *
 * @param string $param Description du paramètre
 * @return bool Description du retour
 * @throws Exception Description de l'exception
 */
```

## 🎨 Standards de code

### PSR-12

Le code doit respecter le standard PSR-12:
- Indentation: 4 espaces
- Accolades: style Allman pour les classes, K&R pour les méthodes
- Une instruction par ligne
- Pas de trailing whitespace

### Documentation PHPDoc

Toutes les classes, méthodes et propriétés doivent être documentées:

```php
/**
 * Brève description de la classe
 *
 * Description détaillée si nécessaire
 *
 * @package Controllers\User
 * @version 1.0
 * @author Votre nom
 */
class MyController implements ControllerInterface
{
    /**
     * Description de la propriété
     *
     * @var string
     */
    private string $property;

    /**
     * Description de la méthode
     *
     * Description détaillée du comportement
     *
     * @param string $param Description du paramètre
     * @return bool Description de ce qui est retourné
     * @throws Exception Quand une exception est levée
     */
    public function myMethod(string $param): bool
    {
        // Code
    }
}
```

## 🚨 Désactiver temporairement les hooks

Si vous devez commiter sans vérification (déconseillé):

```bash
git commit --no-verify -m "Message"
```

## 🔄 Workflow de développement recommandé

1. **Créer une branche**
   ```bash
   git checkout -b feature/ma-feature
   ```

2. **Développer avec tests**
   ```bash
   # Écrire le code
   # Écrire les tests
   make test
   ```

3. **Vérifier la qualité**
   ```bash
   make quality
   ```

4. **Corriger si nécessaire**
   ```bash
   make fix
   make quality
   ```

5. **Commiter** (le hook pre-commit s'exécute automatiquement)
   ```bash
   git add .
   git commit -m "feat: ma nouvelle fonctionnalité"
   ```

6. **Push et créer une PR**
   ```bash
   git push origin feature/ma-feature
   ```

7. **GitHub Actions** vérifie automatiquement

## 📈 Métriques de qualité

### Couverture par type

- **Controllers**: 60%+ (logique métier testée)
- **Models**: 80%+ (logique critique)
- **Utilis**: 90%+ (utilitaires réutilisables)
- **Views**: Non testé (templates HTML)

### Complexité cyclomatique

- **Maximum acceptable**: 10
- **Recommandé**: < 5

PHPStan vous alertera si une méthode est trop complexe.

## 🐛 Troubleshooting

### Les hooks ne s'exécutent pas

```bash
# Vérifier que le fichier est exécutable
chmod +x .git/hooks/pre-commit

# Vérifier le contenu
cat .git/hooks/pre-commit
```

### PHPUnit ne trouve pas les tests

```bash
# Vérifier la structure
ls -la tests/Unit/
ls -la tests/Integration/

# Vérifier le bootstrap
php tests/bootstrap.php
```

### Erreur "command not found"

```bash
# Réinstaller les dépendances
rm -rf vendor/
composer install
```

### Les tests échouent en CI mais pas en local

```bash
# Simuler l'environnement CI
make ci

# Vérifier les variables d'environnement
cat .env
```

## 📚 Ressources

- [PSR-12](https://www.php-fig.org/psr/psr-12/)
- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [PHPStan Documentation](https://phpstan.org/user-guide/getting-started)
- [PHP CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer)

## 🎯 Checklist avant de merger

- [ ] Tous les tests passent (`make test`)
- [ ] Couverture de code acceptable (`make coverage`)
- [ ] Code style respecté (`make phpcs`)
- [ ] Analyse statique OK (`make phpstan`)
- [ ] Documentation complète (`make phpdoc`)
- [ ] Pas de vulnérabilités (`composer audit`)
- [ ] GitHub Actions ✅ vert
- [ ] Code review approuvé

## �� Bonnes pratiques

1. **Tests d'abord** (TDD)
   - Écrire le test avant le code
   - Red → Green → Refactor

2. **Commits atomiques**
   - Un commit = une fonctionnalité
   - Messages explicites

3. **Documentation à jour**
   - Documenter au fur et à mesure
   - Pas de "TODO" en production

4. **Revue de code**
   - Relire son propre code avant de push
   - Demander une review pour les gros changements

5. **Exécuter les vérifications localement**
   - Ne pas attendre la CI pour détecter les erreurs
   - Utiliser `make quality` régulièrement
