
# ARCHITECTURE TECHNIQUE
## Application web de gestion d'un cabinet comptable

## 1. Introduction

L'application de gestion d'un cabinet comptable est développée selon une architecture MVC (Modèle-Vue-Contrôleur).

Cette architecture permet de séparer les traitements métier, la gestion des données et l'affichage des interfaces. Elle facilite ainsi la maintenance, l'évolution et la réutilisation du code.

## 2. Présentation de l'architecture MVC

L'architecture MVC repose sur trois composants principaux.

### 2.1 Le Modèle (Model)

Le modèle représente la partie responsable de la gestion des données.

Il assure notamment :
- La communication avec la base de données.
- L'exécution des requêtes SQL.
- La récupération des informations.
- L'enregistrement et la modification des données.
- La suppression des informations autorisées.

Dans le projet, les modèles se trouvent dans :

`app/Models/`

Exemples :
- User.php
- Company.php
- Exercise.php
- Account.php
- Journal.php
- Entry.php
- Invoice.php

### 2.2 La Vue (View)

La vue représente l'interface visible par l'utilisateur.

Elle permet d'afficher :
- Les formulaires.
- Les tableaux de données.
- Les tableaux de bord.
- Les statistiques.
- Les notifications.
- Les documents comptables.

Les vues sont organisées dans :

`app/Views/`

Elles sont réparties selon les modules et les profils utilisateurs.

### 2.3 Le Contrôleur (Controller)

Le contrôleur reçoit les requêtes de l'utilisateur et coordonne les traitements.

Il peut :
- Vérifier les données reçues.
- Appeler les modèles.
- Utiliser les services métier.
- Contrôler les autorisations.
- Sélectionner la vue à afficher.
- Retourner une réponse adaptée.

Les contrôleurs sont situés dans :

`app/Controllers/`

## 3. Organisation générale du projet

Le projet est organisé de la manière suivante :

```text
cabinet-comptable/
│
├── app/
│   ├── Controllers/
│   ├── Models/
│   ├── Core/
│   ├── Middleware/
│   ├── Services/
│   ├── Validators/
│   └── Views/
│
├── config/
│
├── database/
│   ├── migrations/
│   ├── seeders/
│   └── cabinet_comptable.sql
│
├── public/
│   ├── assets/
│   │   ├── css/
│   │   ├── js/
│   │   ├── images/
│   │   └── icons/
│   ├── index.php
│   └── .htaccess
│
├── routes/
│   ├── web.php
│   └── api.php
│
├── storage/
│   ├── documents/
│   ├── exports/
│   ├── backups/
│   └── logs/
│
├── tests/
│   ├── Unit/
│   └── Integration/
│
├── docs/
│
├── .env.example
├── .gitignore
└── README.md
```

## 4. Description des principaux composants

### 4.1 Le dossier Core

Le dossier `app/Core` contient les composants fondamentaux de l'application.

| Fichier | Responsabilité |
|---|---|
| Application.php | Initialise et exécute l'application |
| Router.php | Gère les routes |
| Controller.php | Classe de base des contrôleurs |
| Model.php | Classe de base des modèles |
| Database.php | Connexion à la base de données |
| Auth.php | Gestion de l'authentification |
| Session.php | Gestion des sessions |
| Request.php | Gestion des requêtes |
| Response.php | Gestion des réponses |
| Validator.php | Validation des données |
| Helpers.php | Fonctions utilitaires |

### 4.2 Le dossier Middleware

Les middlewares contrôlent les requêtes avant leur traitement.

Ils permettent notamment :
- De vérifier l'authentification.
- De contrôler les rôles.
- De limiter l'accès aux entreprises.
- De protéger les formulaires.
- De contrôler l'exercice comptable actif.

### 4.3 Le dossier Services

Les services regroupent les traitements métier.

Exemples :
- AccountingService : opérations comptables.
- LedgerService : gestion du grand livre.
- BalanceService : calcul des soldes.
- TaxCalculationService : calculs fiscaux.
- InvoiceService : gestion des factures.
- ExportService : exportation des données.

### 4.4 Le dossier Validators

Les validateurs contrôlent les informations saisies avant leur traitement.

Ils permettent de vérifier la conformité des données utilisateurs, des entreprises, des exercices et des écritures comptables.

## 5. Fonctionnement d'une requête

Le fonctionnement général d'une requête suit les étapes suivantes :

1. L'utilisateur effectue une action dans son navigateur.
2. La requête est reçue par le point d'entrée `public/index.php`.
3. Le routeur identifie la route correspondante.
4. Les middlewares vérifient les conditions d'accès.
5. Le contrôleur reçoit la requête.
6. Le contrôleur appelle les modèles ou les services nécessaires.
7. Les modèles communiquent avec la base de données.
8. Les résultats sont transmis à la vue.
9. La vue affiche les informations à l'utilisateur.

## 6. Gestion de la base de données

La base de données utilisée est MySQL ou MariaDB.

Elle centralise les informations relatives :
- Aux utilisateurs.
- Aux rôles.
- Aux entreprises.
- Aux associations entre utilisateurs et entreprises.
- Aux exercices comptables.
- Aux comptes.
- Aux journaux.
- Aux écritures.
- Aux lignes comptables.
- Aux taxes.
- Aux documents.
- Aux notifications.
- Aux journaux d'audit.
- Aux factures.

L'accès aux données est réalisé à l'aide de PDO afin de faciliter les échanges entre PHP et le système de gestion de base de données.

## 7. Sécurité de l'architecture

Plusieurs mécanismes sont prévus :

- Utilisation de mots de passe hachés.
- Requêtes préparées avec PDO.
- Contrôle des rôles et permissions.
- Protection contre les attaques CSRF.
- Validation des données reçues.
- Contrôle des accès aux dossiers des entreprises.
- Gestion sécurisée des sessions.
- Journalisation des opérations importantes.

## 8. Technologies utilisées

| Technologie | Rôle |
|---|---|
| PHP | Langage serveur |
| HTML | Structure des pages |
| CSS | Présentation graphique |
| JavaScript | Interactions |
| Bootstrap | Composants d'interface |
| MySQL/MariaDB | Stockage des données |
| Apache | Serveur web |
| WampServer | Environnement de développement |
| UML | Modélisation |

## 9. Conclusion

L'architecture MVC retenue permet d'organiser l'application en composants distincts et complémentaires.

Cette organisation facilite la compréhension du code, la maintenance du système et l'ajout de nouvelles fonctionnalités selon les besoins du cabinet comptable.
```

**À faire maintenant :**
1. Ouvre `docs/architecture.md`.
2. Colle le contenu complet.
3. Enregistre avec `Ctrl + S`.

Lorsque c'est terminé, réponds **oui**. Nous passerons à `docs/database-schema.md`.