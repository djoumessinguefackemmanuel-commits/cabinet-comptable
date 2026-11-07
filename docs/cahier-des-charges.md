
# CAHIER DES CHARGES
## Application web de gestion d'un cabinet comptable

**Nom du projet :** Cabinet Comptable  
**Version :** 1.0  
**Type d'application :** Application web de gestion comptable  
**Architecture :** Modèle-Vue-Contrôleur (MVC)

---

## 1. Présentation générale du projet

Le projet consiste à concevoir et réaliser une application web destinée à faciliter la gestion des activités d'un cabinet comptable.

L'application permettra de centraliser les informations relatives aux entreprises clientes, aux exercices comptables, aux comptes, aux journaux, aux écritures comptables, aux déclarations fiscales et aux documents financiers.

Elle vise à améliorer l'organisation du travail, à réduire les erreurs de saisie et à faciliter le suivi des opérations comptables.

## 2. Contexte et justification

La gestion comptable d'un cabinet nécessite le traitement régulier d'un volume important d'informations.

L'utilisation de méthodes manuelles ou de plusieurs fichiers indépendants peut entraîner des difficultés telles que :

- La perte ou la duplication des informations.
- Les erreurs dans les écritures comptables.
- Les difficultés de recherche des documents.
- Le retard dans la production des états financiers.
- Le manque de suivi des échéances fiscales.
- Les difficultés de contrôle des opérations réalisées.

La mise en place d'une application centralisée permettra de mieux organiser ces différentes activités.

## 3. Objectifs du projet

### 3.1 Objectif général

Développer une application web sécurisée permettant de gérer efficacement les principales activités administratives, comptables et fiscales d'un cabinet comptable.

### 3.2 Objectifs spécifiques

- Gérer les utilisateurs et leurs rôles.
- Enregistrer et gérer les entreprises clientes.
- Organiser les exercices comptables.
- Gérer le plan comptable.
- Gérer les journaux comptables.
- Enregistrer et contrôler les écritures comptables.
- Calculer les soldes et produire les balances.
- Générer les états financiers.
- Suivre les opérations fiscales.
- Gérer les factures et les documents.
- Produire des exports de données.
- Conserver une trace des opérations importantes.

## 4. Identification des acteurs

L'application prévoit quatre principaux profils d'utilisateurs.

### 4.1 Administrateur

L'administrateur assure la gestion générale de la plateforme.

Ses responsabilités sont :
- Créer et gérer les comptes utilisateurs.
- Attribuer les rôles et les permissions.
- Consulter les entreprises enregistrées.
- Superviser les activités.
- Consulter les journaux d'audit.
- Gérer les paramètres de l'application.

### 4.2 Expert-comptable

L'expert-comptable supervise les opérations comptables.

Ses responsabilités sont :
- Consulter les dossiers des entreprises.
- Contrôler les écritures comptables.
- Valider les opérations.
- Consulter les balances.
- Produire les états financiers.
- Suivre les déclarations fiscales.

### 4.3 Comptable

Le comptable réalise les opérations quotidiennes.

Ses responsabilités sont :
- Enregistrer les entreprises qui lui sont accessibles.
- Gérer les exercices comptables.
- Saisir les écritures.
- Consulter les comptes et les journaux.
- Effectuer les rapprochements.
- Préparer les documents comptables.

### 4.4 Client

Le client représente une entreprise ou un responsable autorisé à accéder à son dossier.

Ses responsabilités sont :
- Consulter les informations de son entreprise.
- Consulter les documents mis à sa disposition.
- Consulter les factures.
- Suivre les notifications.
- Accéder aux informations financières autorisées.

## 5. Fonctionnalités principales

### 5.1 Authentification et sécurité

- Connexion par adresse électronique et mot de passe.
- Déconnexion sécurisée.
- Gestion des sessions.
- Réinitialisation du mot de passe.
- Contrôle des permissions.
- Protection contre les accès non autorisés.
- Protection des formulaires contre les attaques CSRF.

### 5.2 Gestion des utilisateurs

- Création d'utilisateurs.
- Modification des informations.
- Activation et désactivation des comptes.
- Attribution des rôles.
- Consultation des utilisateurs.

### 5.3 Gestion des entreprises

- Enregistrement d'une entreprise.
- Modification des informations.
- Consultation des détails.
- Association des utilisateurs aux entreprises.
- Gestion des accès aux dossiers.

### 5.4 Gestion des exercices comptables

- Création d'un exercice.
- Définition des dates de début et de fin.
- Consultation des exercices.
- Ouverture et clôture des exercices.
- Suivi des opérations par exercice.

### 5.5 Gestion comptable

- Gestion du plan comptable.
- Gestion des journaux.
- Création des écritures.
- Ajout des lignes comptables.
- Vérification de l'équilibre débit/crédit.
- Validation des écritures.
- Consultation du grand livre.
- Production de la balance.

### 5.6 Gestion fiscale

- Gestion des informations fiscales.
- Calcul de la TVA.
- Suivi des déclarations.
- Gestion des échéances fiscales.
- Consultation des informations fiscales.

### 5.7 Gestion des états financiers

L'application doit permettre de consulter et de produire :

- Le bilan.
- Le compte de résultat.
- La balance générale.
- Le grand livre.

### 5.8 Gestion documentaire et facturation

- Enregistrement des documents.
- Classement des pièces justificatives.
- Gestion des factures.
- Consultation des factures.
- Exportation des documents.

### 5.9 Notifications et traçabilité

- Affichage des notifications.
- Suivi des événements importants.
- Conservation de l'historique des opérations.
- Consultation des journaux d'audit.

## 6. Contraintes techniques

L'application sera développée avec les technologies suivantes :

| Technologie | Utilisation |
|---|---|
| HTML5 | Structure des pages |
| CSS3 | Mise en forme |
| JavaScript | Interactions côté navigateur |
| Bootstrap | Mise en page et composants |
| PHP natif | Traitement côté serveur |
| MySQL/MariaDB | Gestion de la base de données |
| PDO | Communication avec la base de données |
| Apache | Serveur web |
| WampServer | Environnement local |
| UML | Modélisation du système |
| Visual Studio Code | Environnement de développement |

## 7. Architecture de l'application

L'application adoptera l'architecture MVC (Modèle-Vue-Contrôleur).

- **Modèle :** gère les données et les échanges avec la base de données.
- **Vue :** présente les interfaces à l'utilisateur.
- **Contrôleur :** reçoit les requêtes et coordonne les traitements.

Cette architecture permettra de séparer les responsabilités et de faciliter la maintenance du système.

## 8. Exigences non fonctionnelles

L'application devra respecter les exigences suivantes :

- Sécurité : protéger les données et les accès.
- Fiabilité : assurer la cohérence des opérations comptables.
- Ergonomie : proposer une interface claire et facile à utiliser.
- Performance : afficher rapidement les informations.
- Maintenabilité : faciliter les corrections et évolutions.
- Compatibilité : fonctionner sur les navigateurs web modernes.
- Traçabilité : conserver l'historique des opérations importantes.

## 9. Résultats attendus

À la fin du projet, le cabinet devra disposer d'une application web permettant de centraliser la gestion des entreprises, des utilisateurs, des opérations comptables, des informations fiscales et des documents.

L'application devra également faciliter le contrôle des écritures, la production des états financiers et le suivi des activités du cabinet.

## 10. Conclusion

Ce cahier des charges définit les principales orientations fonctionnelles et techniques du projet. Il servira de référence pendant les différentes étapes de conception, de développement, de test et de déploiement de l'application.
