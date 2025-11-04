# SAEManager

## **1. Analyse des Besoins & Spécifications**
### **a. Besoins du Client (Pages 6-7)**  
- **Utilisateurs & Rôles** : Étudiants, Responsables de SAE, Enseignants/Clients, Administrateurs.  
- **Fonctionnalités Clés** :  
  - Gestion simplifiée des évaluations/compétences (notes, commentaires sur les livrables).  
  - Suivi des livrables (lien vers Ametice, tri par groupe SAE/type de livrable).  
  - Suivi des SAE (tableau de bord Kanban/to-do list, calendrier des échéances).  
  - Notifications automatisées (push + email, 3 jours avant les échéances).  
  - Authentification simple (login/mot de passe) avec option double auth plus tard.  
- **UI/UX** : Interface simple, épurée, respectant la charte graphique de l'IUT.  

### **b. Exigences Académiques (Pages 1-5)**  
- **Architecture** : Modèle MVC, utilisation de PDO pour les opérations CRUD, pagination.  
- **Sécurité** : Conformité au Top 10 OWASP.  
- **Qualité** : Accessibilité, responsive design, bonnes pratiques de développement (commentaires, validation W3C, optimisation).  
- **Livraisons** : Maquettes Figma, code GitHub, documentation, présentation orale.


## **2. Plan de Projet (Approche Itérative)**
Le client souhaite un développement itératif. On priorise un **MVP (Minimum Viable Product)** puis des itérations progressives.

| **Phase**       | **Objectifs**                                                                 | **Livraison**                                  |
|------------------|-------------------------------------------------------------------------------|-----------------------------------------------|
| **1. Conception** | - Réaliser des maquettes Figma pour tous les rôles.<br>- Définir la base de données (tables : utilisateurs, SAE, livrables, évaluations, notifications). | Maquettes Figma, schéma de base de données.    |
| **2. Développement MVP** | - Authentification (inscription, connexion, mot de passe oublié).<br>- Gestion basique des SAE (création, visualisation).<br>- Téléchargement/liens des livrables.<br>- Calendrier des échéances. | Code fonctionnel (MVC), base de données opérationnelle. |
| **3. Itération 1** | - Système de notation/évaluation (interface pour les enseignants).<br>- Notifications basiques (email). | Fonctionnalités d’évaluation, module de notification. |
| **4. Itération 2** | - Tableau de bord Kanban pour le suivi des SAE.<br>- Intégration de notifications push.<br>- Amélioration de l’UI (charte IUT). | Dashboard dynamique, notifications push.       |
| **5. Tests & Validation** | - Tests unitaires (PHPUnit), tests d’accessibilité, vérification OWASP.<br>- Correction des bugs. | Rapport de test, version stable.               |
| **6. Déploiement & Documentation** | - Hébergement du site (accès public).<br>- Documentation (PHPDoc, guide utilisateur).<br>- Préparation de la présentation orale. | Site en ligne, documentation complète.         |


## **3. Technologies Recommandées**
- **Frontend** : HTML5, CSS3 (responsive), JavaScript (pour les interactions).  
- **Backend** : PHP (framework minimaliste ou vanilla, conformément aux exigences MVC).  
- **Base de Données** : MySQL (avec PDO pour les requêtes).  
- **Outils** :  
  - Figma (maquettes).  
  - Git (versionnement).  
  - PHPUnit (tests unitaires).  
  - Cron Jobs (planification des notifications).  


## **4. Points Critiques à Respecter**
- **Sécurité** : Valider les entrées utilisateur, utiliser des mots de passe hashés, protéger contre les failles courantes (XSS, injection SQL).  
- **Accessibilité** : Utiliser des balises sémantiques, tester avec des outils comme Wave ou Axe.  
- **Performance** : Optimiser les images, minifier le CSS, utiliser le caching.  
- **Respect des Délais** : Planifier les réunions futures (page 7) pour valider les itérations.  


## **5. Livrables Attendus**
- URL du site web.  
- URL du dépôt Git.  
- Maquettes Figma partagées.  
- Identifiants de connexion (site/base de données).  
- Liste des sources utilisées (y compris les IA).  
- Présentation orale (démonstration du projet).  



## Diagramme UML

[Diagramme de classe du projet](interactive-uml.html)


## Tableau des cas d'utilisations

| | Client  | Student | Teacher | New customer |
|-|-|-|-|-|
| Accès aux SAE | ✅ | ✅ | ✅ | ❌ |
| Contacter les membres | ✅ | ✅ | ✅ | ❌ |
| Suivre Progression | ✅ | ✅ | ✅  | ❌ |
| Attribuer SAE | ❌ | ❌ | ✅ | ❌ |
| Créer SAE |  ❌ | ❌ | ✅ | ❌ |
| Authentification | ❌ | ❌ | ❌ | ✅ |

## Diagramme de cas
``` mermaid
flowchart LR
  rW["👤 Web Customer"]:::role
  rR["👤 Registered Customer"]:::role
  rN["👤 New Customer"]:::role
  rM["👤 << service >> Mail Server"]:::role

  subgraph S["SAEManager"]
    ucVS([View SAE])
    ucCM([Contact Members of the group])
    ucFP([Follow Progress of the group])
    ucAS([Attribute Students to SAE])
    ucCS([Create SAE])
    ucA([Authentification])
    ucSM([Send Mail to Students])
    ucCM -. include .-> ucSM
    ucAS -. include .-> ucSM

  end
  

  rW --- rR
  rW --- rN
  rR --- ucVS
  rR --- ucCM
  rR --- ucFP
  rR --- ucAS
  rR --- ucCS

  rN --- ucA

  ucSM --- rM
``` 