.PHONY: help install test coverage phpcs phpstan quality fix hooks

# Couleurs
GREEN=\033[0;32m
YELLOW=\033[1;33m
NC=\033[0m

help: ## Affiche cette aide
	@echo "Commandes disponibles:"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "  ${GREEN}%-15s${NC} %s\n", $$1, $$2}'

install: ## Installe les dépendances
	@echo "${YELLOW}Installation des dépendances...${NC}"
	composer install
	@echo "${GREEN}✓ Dépendances installées${NC}"

hooks: ## Installe les Git hooks
	@echo "${YELLOW}Installation des hooks Git...${NC}"
	chmod +x .git/hooks/pre-commit
	@echo "${GREEN}✓ Hooks installés${NC}"

test: ## Lance les tests unitaires
	@echo "${YELLOW}Exécution des tests...${NC}"
	./vendor/bin/phpunit --testdox

test-unit: ## Lance uniquement les tests unitaires
	@echo "${YELLOW}Tests unitaires...${NC}"
	./vendor/bin/phpunit --testsuite="Unit Tests" --testdox

test-integration: ## Lance uniquement les tests d'intégration
	@echo "${YELLOW}Tests d'intégration...${NC}"
	./vendor/bin/phpunit --testsuite="Integration Tests" --testdox

coverage: ## Génère le rapport de couverture
	@echo "${YELLOW}Génération du rapport de couverture...${NC}"
	./vendor/bin/phpunit --coverage-html coverage --coverage-text
	@echo "${GREEN}✓ Rapport disponible dans: coverage/index.html${NC}"

phpcs: ## Vérifie le code style (PSR-12)
	@echo "${YELLOW}Vérification du code style...${NC}"
	./vendor/bin/phpcs --standard=phpcs-phpdoc.xml --standard=PSR12 --colors App/src/ Core/

phpstan: ## Lance l'analyse statique
	@echo "${YELLOW}Analyse statique...${NC}"
	./vendor/bin/phpstan analyse . --level=8


generate-phpdoc: ## Génère la documentation
	@echo "${YELLOW}Génération de la documentation...${NC}"
	./vendor/bin/phpdoc --directory=App/src/,Core/ --target=docs/api --template=clean --title='SAE Manager API Documentation' --ignore=vendor/,App/tests/ --visibility=public,protected --defaultpackagename=SAEManager

phpdoc: ## Vérifie la documentation
	@echo "${YELLOW}Vérification de la documentation...${NC}"
	./vendor/bin/phpcs --standard=phpcs-phpdoc.xml --colors App/src/
	./vendor/bin/phpcs --standard=phpcs-phpdoc.xml --colors Core/

generate-uml: ## Génère les diagrammes de classes
	@echo "${YELLOW}Génération des diagrammes de classes...${NC}"
	./vendor/bin/php-class-diagram --exclude='Validator' \
	--svg-topurl='https://github.com/RADJOU-Dinesh-24003262/SAEManager/tree/dev/App/src' \
	App/src  > asset/UML/class-diagram.puml
	java -jar plantuml.jar -tsvg asset/UML/class-diagram.puml


fix: ## Corrige automatiquement les erreurs de style
	@echo "${YELLOW}Correction automatique...${NC}"
	./vendor/bin/phpcbf --standard=phpcs-phpdoc.xml --standard=PSR12 App/
	./vendor/bin/phpcbf --standard=phpcs-phpdoc.xml --standard=PSR12 Core/
	./vendor/bin/php-cs-fixer fix App/
	./vendor/bin/php-cs-fixer fix Core/
	@echo "${GREEN}✓ Code formaté${NC}"

quality: ## Lance toutes les vérifications de qualité
	@echo "${YELLOW}=== Vérifications de qualité ===${NC}\n"
	@make phpcs
	@echo ""
	@make phpstan
	@echo ""
	@make phpdoc
	@echo ""
	@make test
	@echo "\n${GREEN}✅ Toutes les vérifications sont passées!${NC}"

ci: ## Simule le pipeline CI en local
	@echo "${YELLOW}=== Simulation du pipeline CI ===${NC}\n"
	@make quality
	@make coverage
	@echo "\n${GREEN}✅ Pipeline CI simulé avec succès!${NC}"

clean: ## Nettoie les fichiers temporaires
	@echo "${YELLOW}Nettoyage...${NC}"
	rm -rf coverage/
	rm -f coverage.xml
	@echo "${GREEN}✓ Nettoyage terminé${NC}"

fix-staged: ## Corrige les fichiers stagés en fonction du code style (PSR-12)
	@echo "🔧 Correction des fichiers PHP en staging avec PHP-CS-Fixer..."
	@FILES=$$(git diff --cached --name-only --diff-filter=ACM | grep '\.php$$'); \
	if [ -z "$$FILES" ]; then \
		echo "✅ Aucun fichier PHP à corriger."; \
	else \
		for FILE in $$FILES; do \
			echo "➡ Correction: $$FILE"; \
			vendor/bin/php-cs-fixer fix --using-cache=no "$$FILE"; \
			vendor/bin/phpcbf --standard=PSR12 --standard=phpcs-phpdoc.xml --colors "$$FILE"; \
			git add "$$FILE"; \
		done; \
		echo "✅ Tous les fichiers ont été corrigés et re-stagés."; \
	fi