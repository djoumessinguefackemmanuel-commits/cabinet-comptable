
# SCHÉMA DE LA BASE DE DONNÉES
## Application web de gestion d'un cabinet comptable

## 1. Présentation

La base de données `cabinet_comptable` permet de stocker et d'organiser toutes les informations nécessaires au fonctionnement de l'application.

Elle utilise MySQL ou MariaDB et les échanges avec PHP sont effectués à travers PDO.

La structure est organisée autour de plusieurs tables liées entre elles afin de garantir la cohérence des informations.

## 2. Liste des principales tables

| Table | Description |
|---|---|
| roles | Enregistre les rôles des utilisateurs |
| users | Contient les comptes utilisateurs |
| companies | Contient les entreprises clientes |
| user_companies | Associe les utilisateurs aux entreprises |
| exercises | Enregistre les exercices comptables |
| accounts | Contient le plan comptable |
| journals | Contient les journaux comptables |
| entries | Enregistre les écritures comptables |
| entry_lines | Contient les lignes des écritures |
| taxes | Stocke les informations fiscales |
| documents | Enregistre les documents |
| notifications | Stocke les notifications |
| audit_logs | Conserve les traces des opérations |
| invoices | Enregistre les factures |

## 3. Description des tables

### 3.1 Table roles

Cette table contient les différents profils d'accès à l'application.

Informations principales :
- Identifiant du rôle.
- Nom du rôle.
- Description.
- Date de création.

Exemples de rôles :
- Administrateur.
- Expert-comptable.
- Comptable.
- Client.

### 3.2 Table users

Elle contient les informations relatives aux utilisateurs.

Informations principales :
- Identifiant.
- Rôle.
- Nom.
- Prénom.
- Adresse électronique.
- Mot de passe haché.
- Statut du compte.
- Date de création.
- Date de modification.

### 3.3 Table companies

Elle contient les informations des entreprises clientes.

Informations principales :
- Identifiant.
- Nom de l'entreprise.
- Numéro d'identification fiscale.
- Adresse.
- Téléphone.
- Adresse électronique.
- Date de création.

### 3.4 Table user_companies

Cette table permet d'associer les utilisateurs aux entreprises auxquelles ils ont accès.

Elle facilite la gestion des autorisations et le suivi des dossiers.

### 3.5 Table exercises

Elle contient les exercices comptables des entreprises.

Informations principales :
- Identifiant.
- Entreprise concernée.
- Libellé.
- Date de début.
- Date de fin.
- Statut.

### 3.6 Table accounts

Elle contient les comptes du plan comptable.

Informations principales :
- Identifiant.
- Entreprise concernée.
- Exercice comptable associé, selon la structure retenue.
- Numéro du compte.
- Intitulé.
- Type de compte.
- Statut.

### 3.7 Table journals

Elle contient les journaux utilisés pour classer les opérations comptables.

Exemples :
- Journal des achats.
- Journal des ventes.
- Journal de banque.
- Journal de caisse.
- Journal des opérations diverses.

### 3.8 Table entries

Elle représente les écritures comptables.

Informations principales :
- Identifiant.
- Référence.
- Entreprise.
- Exercice comptable.
- Journal.
- Date de l'écriture.
- Libellé.
- Statut.
- Utilisateur ayant créé l'écriture.

### 3.9 Table entry_lines

Elle contient les différentes lignes d'une écriture comptable.

Chaque ligne comporte notamment :
- Le compte comptable.
- Le libellé.
- Le montant au débit.
- Le montant au crédit.

Une écriture doit respecter le principe de la partie double : le total des débits doit être égal au total des crédits.

### 3.10 Table taxes

Cette table conserve les informations nécessaires à la gestion fiscale.

Elle permet notamment d'organiser les données relatives aux taxes et aux déclarations.

### 3.11 Table documents

Elle référence les documents associés aux entreprises et aux opérations comptables.

Les fichiers physiques sont conservés dans le dossier de stockage prévu à cet effet.

### 3.12 Table notifications

Elle conserve les notifications destinées aux utilisateurs.

### 3.13 Table audit_logs

Elle enregistre les événements importants de l'application afin de faciliter le contrôle et la traçabilité.

### 3.14 Table invoices

Elle contient les informations relatives aux factures émises dans le cadre des prestations du cabinet.

## 4. Relations entre les tables

Les principales relations sont les suivantes :

- Un rôle peut être attribué à plusieurs utilisateurs.
- Un utilisateur peut être associé à plusieurs entreprises.
- Une entreprise peut être associée à plusieurs utilisateurs.
- Une entreprise peut posséder plusieurs exercices comptables.
- Une entreprise peut disposer de plusieurs comptes et journaux.
- Un exercice peut contenir plusieurs écritures.
- Un journal peut regrouper plusieurs écritures.
- Une écriture peut contenir plusieurs lignes comptables.
- Un compte peut être utilisé dans plusieurs lignes comptables.
- Une entreprise peut posséder plusieurs documents et factures.
- Un utilisateur peut recevoir plusieurs notifications.
- Un utilisateur peut effectuer plusieurs opérations enregistrées dans les journaux d'audit.

## 5. Schéma conceptuel simplifié

```text
ROLES
  |
  | 1,N
  |
USERS
  |
  | N,N
  |
USER_COMPANIES
  |
  |
COMPANIES
  |
  | 1,N
  |
EXERCISES
  |
  | 1,N
  |
ENTRIES -------- JOURNALS
  |
  | 1,N
  |
ENTRY_LINES
  |
  | N,1
  |
ACCOUNTS

COMPANIES -------- DOCUMENTS
COMPANIES -------- INVOICES
USERS ------------ NOTIFICATIONS
USERS ------------ AUDIT_LOGS
```

Ce schéma présente les principales entités du système. Les relations exactes sont définies par les clés primaires et étrangères dans les migrations SQL.

## 6. Intégrité des données

Pour assurer la cohérence des informations, la base de données utilise :

- Des clés primaires pour identifier les enregistrements.
- Des clés étrangères pour relier les tables.
- Des contraintes d'unicité lorsque nécessaire.
- Des champs obligatoires pour les informations essentielles.
- Des transactions SQL pour les opérations qui nécessitent plusieurs modifications.
- Des contrôles applicatifs sur les écritures comptables.

## 7. Sécurité des données

L'application prévoit plusieurs mesures :

- Hachage des mots de passe.
- Utilisation de requêtes préparées.
- Vérification des droits d'accès.
- Protection des données sensibles.
- Sauvegarde régulière de la base de données.
- Journalisation des opérations importantes.

## 8. Gestion des migrations

Les migrations SQL sont organisées dans le dossier :

`database/migrations/`

Elles permettent de créer progressivement les tables de la base de données.

Les fichiers sont numérotés de 001 à 014 afin de respecter l'ordre de création des structures.

## 9. Conclusion

La base de données constitue un élément essentiel de l'application. Sa structuration permet de centraliser les informations et de faciliter les opérations administratives, comptables et fiscales du cabinet.

Les relations entre les différentes tables assurent une organisation cohérente des données et facilitent leur exploitation par les différents modules de l'application.
