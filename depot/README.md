
# Cabinet Comptable

## Application web de gestion d'un cabinet comptable

**Version :** 1.0  
**Architecture :** MVC (Modèle-Vue-Contrôleur)  
**Langage serveur :** PHP natif  
**Base de données :** MySQL / MariaDB

---

## 1. Présentation du projet

Cabinet Comptable est une application web destinée à faciliter la gestion des activités administratives, comptables et fiscales d'un cabinet comptable.

Elle permet de centraliser les informations relatives aux entreprises clientes, aux utilisateurs, aux exercices comptables, aux comptes, aux journaux, aux écritures comptables, aux factures et aux documents.

L'objectif est d'améliorer l'organisation du travail, de faciliter le suivi des opérations et de renforcer la traçabilité des informations.

## 2. Fonctionnalités principales

### Authentification et utilisateurs
- Connexion et déconnexion.
- Gestion des utilisateurs.
- Gestion des rôles et permissions.
- Protection des accès.

### Gestion des entreprises
- Enregistrement des entreprises.
- Consultation des dossiers.
- Modification des informations.
- Association des utilisateurs aux entreprises.

### Gestion comptable
- Gestion des exercices comptables.
- Gestion du plan comptable.
- Gestion des journaux.
- Saisie des écritures.
- Contrôle de l'équilibre débit/crédit.
- Consultation du grand livre.
- Production de la balance.

### Gestion fiscale
- Gestion des informations fiscales.
- Calcul de la TVA.
- Suivi des déclarations.
- Suivi des échéances.

### Gestion financière
- Production du bilan.
- Production du compte de résultat.
- Consultation de la balance générale.
- Consultation du grand livre.

### Gestion documentaire
- Gestion des factures.
- Gestion des documents.
- Notifications.
- Historique des opérations.

## 3. Acteurs du système

L'application prévoit quatre profils principaux :

1. Administrateur.
2. Expert-comptable.
3. Comptable.
4. Client.

Chaque profil dispose d'un accès adapté à ses responsabilités et aux permissions qui lui sont attribuées.

## 4. Technologies utilisées

| Technologie | Utilisation |
|---|---|
| HTML5 | Structure des pages |
| CSS3 | Présentation graphique |
| JavaScript | Interactions |
| Bootstrap | Interface utilisateur |
| PHP natif | Traitement serveur |
| MySQL/MariaDB | Base de données |
| PDO | Accès aux données |
| Apache | Serveur web |
| WampServer | Environnement local |
| UML | Modélisation |
| Visual Studio Code | Éditeur de code |

## 5. Architecture du projet

Le projet adopte une architecture MVC.

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
├── storage/
├── tests/
├── docs/
│
├── .env.example
├── .gitignore
└── README.md
```

## 6. Installation

### Prérequis

- Windows.
- WampServer.
- PHP.
- MySQL ou MariaDB.
- Apache.
- Navigateur web.
- Visual Studio Code.

### Étape 1 : placer le projet

Placer le dossier dans :

`C:\wamp64\www\cabinet-comptable`

### Étape 2 : démarrer WampServer

Lancer WampServer et vérifier que les services Apache et MySQL fonctionnent.

### Étape 3 : créer la base de données

Ouvrir phpMyAdmin :

`http://localhost/phpmyadmin`

Créer une base de données nommée :

`cabinet_comptable`

### Étape 4 : importer la base de données

Sélectionner la base de données et importer :

`database/cabinet_comptable.sql`

### Étape 5 : configurer la connexion

Ouvrir :

`config/database.php`

Renseigner les paramètres correspondant à l'installation locale.

### Étape 6 : lancer l'application

Ouvrir le navigateur et accéder à :

`http://localhost/cabinet-comptable/public/`

Pour plus de détails, consulter `docs/installation.md`.

## 7. Compte administrateur de test

Si le seeder administrateur a été exécuté correctement, les identifiants initiaux prévus sont :

- Email : `admin@cabinet-comptable.local`
- Mot de passe : `Admin@12345`

**Important :** modifier ce mot de passe avant toute utilisation en production.

## 8. Documentation

La documentation complémentaire est disponible dans le dossier `docs/` :

- `cahier-des-charges.md` : besoins et fonctionnalités.
- `architecture.md` : organisation technique.
- `database-schema.md` : structure de la base de données.
- `installation.md` : procédure d'installation.
- `user-guide.md` : guide d'utilisation.

## 9. Tests

Les tests sont organisés dans :

- `tests/Unit/`
- `tests/Integration/`

Ils couvrent notamment les opérations comptables, la validation des écritures et certains scénarios d'utilisation.

Les tests fournis constituent une première base de vérification. Leur exécution nécessite un environnement de test compatible.

## 10. Sécurité

L'application prévoit notamment :

- Le hachage des mots de passe.
- Les requêtes préparées avec PDO.
- La gestion des sessions.
- Le contrôle des rôles et permissions.
- La protection CSRF.
- La validation des données.
- La traçabilité des opérations.

## 11. Évolutions possibles

Les évolutions pourront porter sur :
- L'amélioration des tableaux de bord.
- L'automatisation de certains traitements comptables.
- L'amélioration des exports PDF et Excel.
- Le renforcement des contrôles de sécurité.
- L'ajout de nouvelles fonctionnalités selon les besoins du cabinet.

## 12. Conclusion

Cabinet Comptable constitue une solution web destinée à centraliser et faciliter les activités d'un cabinet comptable.

Son architecture modulaire permet d'organiser les fonctionnalités et de faciliter les évolutions futures.
