# SAE Manager

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

## Diagrammes de classes
### Diagramme de classes minimale représentatif du projet. (Ne comprends que des modèles) :
``` mermaid
classDiagram
 direction TB

 class User {
     <<abstract>>
     -email : string
     -name : string
     -user_type : string
     +login()  bool
     +register()  bool
     +updatePassword()  bool
 }

 class Student {
     -amu_id : string
     -year : int
     -specialization : string
     +createToDoItem(ToDoList)  void
     +updateToDoItem(ToDoList)  bool
 }

 class Professor {
    -amu_id : string
     +createSAE()  SAE
     +updateSAE()  bool
     +createGroup()  void
     +assignedGroup()  void
     +removeProfFromSae()  bool
     +addProfToSae()  void
     +unassignedStudentFromSae()  bool
 }

 class Client {
     -organization  string
 }

 class SAE {
     -subject : string
     -description : string
     -start_date : date
     -end_date : date
     +addCompetence()  void
 }

 class ToDoList {
     -task : string
     -is_done : bool
     +markAsDone()  void
 }

 User <|-- Student
 User <|-- Professor
 User <|-- Client

 SAE "1" --> "0..*" Professor : -myResponsable
 SAE "0..*" --> "0..*" Professor : -myViewer

 SAE "0..1" --> "0..*" Client : -myClient

 Student "1" o-- "3..*" SAE : -myStudent[]
 ToDoList "1" *-- "0..1" SAE : -mySae
```
### Représentation simplifiée du MVC autour de la classe User :
``` mermaid
classDiagram
    direction TB

    namespace Controllers {

        class RegisterPost {
            -validator : ValidationServiceRegister
            -user : User
            -registerView : RegisterView
            -successView : RegisterSuccessView
            +control() void
        }
    }

    namespace Validators {
        class FormValidator {
            <<abstract>>
            -required : array
            +validate(data : array) void
            +escape(data : array) array
        }

        class ValidationServiceRegister {
            -required : array
            +validate(data : array) void
        }
    }

    namespace Models {
        class User {
            <<abstract>>
            -email : string
            -name : string
            -user_type : string
            +login() User
            +register() User
            +updatePassword() void
        }
    }

    namespace Views {
        class IndexView {
            -TEMPLATE_HTML : string
            -data : array
            +render() void
        }

        class RegisterView {
            -TEMPLATE_HTML : string
            -data : array
            +render() void
        }

        class RegisterSuccessView {
            -TEMPLATE_HTML : string
            -user : User
            +render() void
        }
    }



RegisterPost --> ValidationServiceRegister : -validator
RegisterPost --> User : -user
RegisterPost --> RegisterView : -registerView
RegisterPost --> RegisterSuccessView : -successView

ValidationServiceRegister --|> FormValidator
RegisterPost --> IndexView
note for IndexView "Redirection par flux"
```
