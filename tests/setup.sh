#!/bin/bash

# Script d'installation automatique des outils de qualité
# Usage: bash setup-quality.sh

set -e

# Couleurs
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}"
echo "╔═══════════════════════════════════════════════════════╗"
echo "║                                                       ║"
echo "║     Configuration des outils de qualité de code      ║"
echo "║                   SAEManager                          ║"
echo "║                                                       ║"
echo "╚═══════════════════════════════════════════════════════╝"
echo -e "${NC}\n"

# Vérifier PHP
echo -e "${YELLOW}🔍 Vérification de PHP...${NC}"
if ! command -v php &> /dev/null; then
    echo -e "${RED}❌ PHP n'est pas installé${NC}"
    exit 1
fi
PHP_VERSION=$(php -r "echo PHP_VERSION;")
echo -e "${GREEN}✓ PHP $PHP_VERSION détecté${NC}\n"

# Vérifier Composer
echo -e "${YELLOW}🔍 Vérification de Composer...${NC}"
if ! command -v composer &> /dev/null; then
    echo -e "${RED}❌ Composer n'est pas installé${NC}"
    echo -e "${YELLOW}Installation: https://getcomposer.org/download/${NC}"
    exit 1
fi
echo -e "${GREEN}✓ Composer détecté${NC}\n"
sudo apt update
sudo apt install php8.4-xml

# Vérifier Git
echo -e "${YELLOW}🔍 Vérification de Git...${NC}"
if ! command -v git &> /dev/null; then
    echo -e "${RED}❌ Git n'est pas installé${NC}"
    exit 1
fi
echo -e "${GREEN}✓ Git détecté${NC}\n"

# Installation des dépendances
echo -e "${YELLOW}📦 Installation des dépendances Composer...${NC}"
composer install --prefer-dist --no-progress
echo -e "${GREEN}✓ Dépendances installées${NC}\n"

# Création des dossiers nécessaires
echo -e "${YELLOW}📁 Création des dossiers de test...${NC}"
mkdir -p tests/Unit
mkdir -p tests/Integration
mkdir -p coverage
echo -e "${GREEN}✓ Dossiers créés${NC}\n"

# Installation du hook pre-commit
echo -e "${YELLOW}🎣 Installation du hook Git pre-commit...${NC}"
if [ -f ".git/hooks/pre-commit" ]; then
    echo -e "${YELLOW}⚠ Hook pre-commit existant, création d'une sauvegarde...${NC}"
    mv .git/hooks/pre-commit .git/hooks/pre-commit.backup
fi

cat > .git/hooks/pre-commit << 'EOF'
#!/bin/bash

# Couleurs
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo -e "${YELLOW}🔍 Vérifications pre-commit...${NC}\n"

PHP_FILES=$(git diff --cached --name-only --diff-filter=ACM | grep '\.php$')

if [ -z "$PHP_FILES" ]; then
    echo -e "${GREEN}✓ Aucun fichier PHP à vérifier${NC}"
    exit 0
fi

ERRORS=0

# Syntaxe PHP
echo -e "${YELLOW}1. Syntaxe PHP...${NC}"
for FILE in $PHP_FILES; do
    php -l "$FILE" > /dev/null 2>&1
    if [ $? -ne 0 ]; then
        echo -e "${RED}✗ Erreur: $FILE${NC}"
        ERRORS=$((ERRORS + 1))
    fi
done
[ $ERRORS -eq 0 ] && echo -e "${GREEN}✓ OK${NC}\n" || echo -e "${RED}✗ Erreurs${NC}\n"

# PSR-12
if command -v phpcs &> /dev/null; then
    echo -e "${YELLOW}2. Code style PSR-12...${NC}"
    phpcs --standard=PSR12 --colors $PHP_FILES
    [ $? -eq 0 ] && echo -e "${GREEN}✓ OK${NC}\n" || ERRORS=$((ERRORS + 1))
fi

# PHPStan
if command -v phpstan &> /dev/null; then
    echo -e "${YELLOW}3. Analyse statique...${NC}"
    phpstan analyse $PHP_FILES --level=5 --no-progress 2>/dev/null
    [ $? -eq 0 ] && echo -e "${GREEN}✓ OK${NC}\n" || ERRORS=$((ERRORS + 1))
fi

if [ $ERRORS -ne 0 ]; then
    echo -e "${RED}❌ Commit refusé ($ERRORS erreur(s))${NC}"
    echo -e "${YELLOW}💡 Utilisez 'make fix' pour corriger automatiquement${NC}"
    exit 1
fi

echo -e "${GREEN}✅ Vérifications OK!${NC}"
exit 0
EOF

chmod +x .git/hooks/pre-commit
echo -e "${GREEN}✓ Hook installé${NC}\n"

# Création d'un test exemple si aucun test n'existe
if [ ! -f "tests/Unit/SessionServiceTest.php" ]; then
    echo -e "${YELLOW}📝 Création d'un test exemple...${NC}"
    cat > tests/Unit/ExampleTest.php << 'EOF'
<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Test example
 */
class ExampleTest extends TestCase
{
    /**
     * @test
     */
    public function it_works(): void
    {
        $this->assertTrue(true);
    }
}
EOF
    echo -e "${GREEN}✓ Test exemple créé${NC}\n"
fi

# Test de l'installation
echo -e "${YELLOW}🧪 Test de l'installation...${NC}"
echo -e "${YELLOW}   - PHPUnit...${NC}"
if ./vendor/bin/phpunit --version &> /dev/null; then
    echo -e "${GREEN}   ✓ PHPUnit OK${NC}"
else
    echo -e "${RED}   ✗ PHPUnit KO${NC}"
fi

echo -e "${YELLOW}   - PHP CodeSniffer...${NC}"
if ./vendor/bin/phpcs --version &> /dev/null; then
    echo -e "${GREEN}   ✓ PHPCS OK${NC}"
else
    echo -e "${RED}   ✗ PHPCS KO${NC}"
fi

echo -e "${YELLOW}   - PHPStan...${NC}"
if ./vendor/bin/phpstan --version &> /dev/null; then
    echo -e "${GREEN}   ✓ PHPStan OK${NC}"
else
    echo -e "${RED}   ✗ PHPStan KO${NC}"
fi

echo ""
echo -e "${GREEN}╔═══════════════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║                                                       ║${NC}"
echo -e "${GREEN}║         ✅ Installation terminée avec succès!         ║${NC}"
echo -e "${GREEN}║                                                       ║${NC}"
echo -e "${GREEN}╚═══════════════════════════════════════════════════════╝${NC}"
echo ""
echo -e "${BLUE}📚 Prochaines étapes:${NC}"
echo -e "   1. Lire le fichier ${YELLOW}README-QUALITE.md${NC}"
echo -e "   2. Exécuter ${YELLOW}make test${NC} pour tester"
echo -e "   3. Exécuter ${YELLOW}make quality${NC} pour vérifier la qualité"
echo -e "   4. Faire un commit pour tester le hook"
echo ""
echo -e "${BLUE}💡 Commandes utiles:${NC}"
echo -e "   ${YELLOW}make help${NC}      - Liste toutes les commandes"
echo -e "   ${YELLOW}make test${NC}      - Lancer les tests"
echo -e "   ${YELLOW}make quality${NC}   - Vérifier la qualité"
echo -e "   ${YELLOW}make fix${NC}       - Corriger le style automatiquement"
echo ""
echo -e "${GREEN}🎉 Bon développement!${NC}"
