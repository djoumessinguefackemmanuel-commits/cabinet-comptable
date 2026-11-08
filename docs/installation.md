
# GUIDE D'INSTALLATION
## Application web de gestion d'un cabinet comptable

## 1. Introduction

Ce document présente les différentes étapes nécessaires à l'installation et à la configuration de l'application de gestion d'un cabinet comptable.

L'application utilise PHP natif, MySQL ou MariaDB, HTML, CSS, JavaScript et Bootstrap.

L'environnement de développement recommandé est WampServer.

## 2. Prérequis techniques

Avant de commencer l'installation, il faut disposer des éléments suivants :

- Un ordinateur sous Windows.
- WampServer installé.
- PHP compatible avec le code source du projet.
- MySQL ou MariaDB.
- Apache.
- Un navigateur web moderne.
- Visual Studio Code pour consulter ou modifier le code.

## 3. Installation de WampServer

1. Télécharger WampServer depuis son site officiel.
2. Exécuter le programme d'installation.
3. Suivre les instructions affichées.
4. Démarrer WampServer.
5. Vérifier que les services Apache et MySQL sont actifs.

L'icône de WampServer doit indiquer que les services fonctionnent correctement.

## 4. Installation du projet

Le projet doit être placé dans le répertoire web de WampServer.

Chemin du projet :

`C:\wamp64\www\cabinet-comptable`

Pour ouvrir le projet dans Visual Studio Code :

1. Lancer Visual Studio Code.
2. Cliquer sur Fichier.
3. Choisir Ouvrir un dossier.
4. Sélectionner le dossier `cabinet-comptable`.

## 5. Configuration de la base de données

### 5.1 Accès à phpMyAdmin

1. Démarrer WampServer.
2. Ouvrir un navigateur.
3. Accéder à l'adresse :

`http://localhost/phpmyadmin`

4. Se connecter avec les identifiants MySQL configurés sur l'ordinateur.

### 5.2 Création de la base de données

Créer une base de données nommée :

`cabinet_comptable`

Choisir de préférence le jeu de caractères `utf8mb4` avec une collation compatible.

### 5.3 Importation du fichier SQL

1. Sélectionner la base `cabinet_comptable`.
2. Ouvrir l'onglet Importer.
3. Choisir le fichier :

`database/cabinet_comptable.sql`

4. Lancer l'importation.
5. Vérifier que les tables ont été créées.

Le fichier SQL doit être importé une seule fois dans une base vide. Après l'import, exécuter également les migrations suivantes (elles sont idempotentes) :

- `database/migrations/004_create_support_requests.sql` pour la boîte de réception des demandes de contact.
- `database/migrations/015_create_password_reset_tokens.sql` pour la réinitialisation sécurisée du mot de passe.

Pour mettre à niveau une base créée avec une ancienne version du schéma des taxes, exécuter également une seule fois `database/migrations/016_add_tax_period_and_due_date.sql`. Ne pas exécuter cette dernière sur une base créée depuis le dump SQL mis à jour, car ses colonnes sont déjà présentes.

### 5.4 Initialisation des rôles et du compte administrateur

Depuis la racine du projet, exécuter les seeders en ligne de commande :

```text
php database/seed.php
```

Cette commande crée les rôles requis et le compte administrateur de test s'il n'existe pas déjà. Elle doit être exécutée après l'import SQL. Ne rendez pas un script d'initialisation accessible depuis le navigateur.

## 6. Configuration locale

Copier `.env.example` vers `.env` à la racine du projet, puis définir les valeurs adaptées à l'installation. Le fichier `.env` est ignoré par Git et ne doit jamais être publié.

Les valeurs de connexion MySQL sont lues depuis `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME` et `DB_PASSWORD`. Le fichier `config/database.php` ne contient que des valeurs de repli pour l'environnement de développement.

`APP_URL` doit correspondre à l'adresse utilisée dans le navigateur, par exemple :

`http://localhost/cabinet-comptable/public`

Pour activer l'envoi des e-mails de réinitialisation via Gmail, configurez dans le fichier `.env` local :

```text
MAIL_MAILER=smtp
MAIL_FROM_ADDRESS=adresse-reelle@gmail.com
MAIL_FROM_NAME="Cabinet Comptable"
MAIL_HOST=smtp.gmail.com
MAIL_PORT=465
MAIL_ENCRYPTION=ssl
MAIL_USERNAME=adresse-reelle@gmail.com
MAIL_PASSWORD=mot-de-passe-d-application-google
```

Activez la validation en deux étapes du compte Google, puis créez un mot de passe d'application. N'utilisez pas le mot de passe habituel du compte et ne placez jamais le secret dans le code, le dépôt ou une conversation. Saisissez-le uniquement dans le `.env` local, ignoré par Git. Installez les dépendances avec `composer install` pour le transport SMTP. Les anciens environnements peuvent conserver `MAIL_MAILER=php` si `mail()` est déjà relié à un serveur de courrier.

Les liens de récupération expirent après une heure et ne peuvent être utilisés qu'une seule fois.

## 7. Configuration de production

1. Déployer l'application sur un serveur Apache/PHP/MySQL maintenu et utiliser un compte MySQL dédié, sans droits d'administration.
2. Créer `.env` à partir du modèle, avec `APP_ENV=production`, `APP_DEBUG=false`, une `APP_URL=https://...`, des identifiants MySQL non vides et une adresse `MAIL_FROM_ADDRESS` réelle. Pour SMTP, fournir aussi les paramètres du relais et installer les dépendances Composer.
3. Configurer le certificat TLS et activer les modules Apache `mod_rewrite` et `mod_authz_core`. L'application redirige vers HTTPS en production et utilise des cookies de session `Secure`, `HttpOnly` et `SameSite`.
4. Le `.htaccess` de la racine refuse les requêtes vers tous les fichiers et dossiers du projet sauf `public/`. Le document root du serveur doit néanmoins pointer directement vers le dossier `public/` lorsque l'hébergeur le permet.
5. Vérifier les droits de fichiers : le compte du serveur web ne doit pouvoir écrire que dans les emplacements nécessaires. Les secrets ne doivent jamais être placés dans un dépôt public.
6. Vérifier les journaux côté serveur et laisser `APP_DEBUG=false`. Les erreurs détaillées ne sont pas destinées aux utilisateurs.

Ne réutilisez pas les identifiants de démonstration décrits ci-dessous en production. Le fichier `.env.example` est un modèle sans secrets.

## 8. Sauvegardes et restauration

Le script `scripts/backup.ps1` crée un dump cohérent MySQL dans `CABINET_BACKUP_DIR`. Ce dossier doit être situé hors du projet web, accessible uniquement aux opérateurs autorisés et inclus dans une politique de rétention/chiffrement adaptée. Le script exige les variables d'environnement `DB_HOST`, `DB_PORT`, `DB_DATABASE` et `DB_USERNAME`. `DB_PASSWORD` peut être omis pour un serveur local sans mot de passe; pour un serveur protégé, définissez-le. En production, utilisez un mot de passe fort et non vide (la configuration de production le vérifie). `MYSQLDUMP_PATH` peut préciser le chemin de `mysqldump.exe`.

Exécutez le script manuellement ou planifiez-le avec le Planificateur de tâches Windows sous un compte dédié disposant des seules permissions nécessaires. Les variables de connexion doivent être configurées dans l'environnement de ce compte, jamais inscrites dans la tâche ou dans le script.

Après chaque changement important, restaurez une copie dans une base de test isolée, vérifiez les données et le fonctionnement de l'application, puis consignez le résultat. Une sauvegarde qui n'a jamais été restaurée n'est pas une procédure de reprise validée. N'effectuez pas de test de restauration sur la base de production.

## 9. Lancement de l'application

Après avoir démarré WampServer, ouvrir le navigateur et saisir :

`http://localhost/cabinet-comptable/public/`

Le point d'entrée de l'application est :

`public/index.php`

Il initialise les composants nécessaires et lance le traitement des requêtes.

**Remarque :** si l'application utilise des routes avec réécriture d'URL, vérifier que le module Apache `mod_rewrite` est activé.

## 10. Compte administrateur initial

Le seeder `database/seeders/AdminSeeder.php`, exécuté par `php database/seed.php`, crée ce compte s'il n'existe pas déjà :

- Adresse électronique : `admin@cabinet-comptable.local`
- Mot de passe : généré aléatoirement et affiché une seule fois dans la console (ou défini via `ADMIN_INITIAL_PASSWORD`, 12 caractères minimum).

Ce compte est réservé à la première configuration. Le compte n'est pas créé par le seul import du fichier SQL ; le seeder doit être exécuté et la base doit être configurée. Si l'adresse existe déjà, le seeder ne remplace pas son mot de passe.

Il est indispensable de modifier le mot de passe avant toute utilisation réelle.

Le formulaire « Contactez votre administrateur » enregistre les demandes dans la base de données et notifie les administrateurs actifs. Un administrateur connecté peut les consulter et mettre à jour leur statut depuis « Demandes de contact » dans le menu latéral. Ce parcours ne dépend pas d'un logiciel de messagerie ni d'une adresse e-mail de démonstration.

Après avoir créé un compte comptable ou client, ouvrir sa fiche depuis « Utilisateurs » puis sélectionner les entreprises auxquelles il doit accéder. Sans association, ces rôles ne voient aucune donnée d'entreprise.

## 11. Vérifications après installation

Après le lancement, effectuer les vérifications suivantes :

1. Vérifier l'affichage de la page de connexion.
2. Tester la connexion administrateur.
3. Vérifier l'accès au tableau de bord.
4. Tester la création d'une entreprise.
5. Vérifier l'accès aux modules comptables.
6. Tester la création d'un exercice.
7. Vérifier l'enregistrement d'une écriture.
8. Contrôler l'équilibre des débits et crédits.
9. Vérifier les notifications et les documents.
10. Envoyer une demande via « Contactez votre administrateur », puis la consulter dans la boîte de réception administrateur.
11. Consulter les journaux d'erreurs en cas de problème.

## 12. Dépannage

### 11.1 Erreur de connexion à la base de données

Vérifier :
- Que MySQL ou MariaDB est démarré.
- Que le nom de la base est correct.
- Que les identifiants sont corrects.
- Que le port de connexion est valide.

### 11.2 Erreur 404

Vérifier :
- Que le projet est dans le bon répertoire.
- Que l'URL saisie est correcte.
- Que les routes sont correctement configurées.
- Que le module `mod_rewrite` est activé si nécessaire.

### 11.3 Erreur 500

Consulter les journaux d'erreurs PHP et Apache.

Vérifier également :
- La syntaxe des fichiers PHP.
- Les chemins des fichiers inclus.
- Les classes chargées automatiquement.
- Les permissions sur les dossiers concernés.

### 11.4 Page blanche

Activer temporairement le mode de débogage dans un environnement local et consulter les journaux PHP.

Ne jamais laisser l'affichage des erreurs détaillées activé en production.

## 13. Recommandations de sécurité

- Modifier les identifiants initiaux.
- Utiliser des mots de passe robustes.
- Ne pas exposer les fichiers de configuration, dumps SQL ou sauvegardes.
- Conserver les documents téléversés et sauvegardes hors de tout chemin web.
- Effectuer des sauvegardes régulières.
- Utiliser HTTPS en production.
- Désactiver le mode de débogage en production.

## 14. Conclusion

Ce guide présente les principales étapes d'installation et de configuration de l'application.

Le respect de ces étapes permet de préparer l'environnement local nécessaire aux tests et à l'utilisation de la plateforme.
