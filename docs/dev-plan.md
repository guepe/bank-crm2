# Dev Plan

## Objectif

Faire avancer `bank-crm` par increments courts, avec une vision partagee entre produit et technique.

Le perimetre visible aujourd'hui dans l'application couvre deja :

- l'authentification et la gestion des utilisateurs
- les comptes, contacts, leads, documents et produits
- un portail client
- un onboarding conversationnel assiste par IA

Ce plan sert de reference legere. Il sera mis a jour au fil des decisions.

## Principes De Travail

- une seule source de verite pour le plan : `docs/`
- une user story ou un petit lot coherent a la fois
- chaque etape doit livrer quelque chose d'utilisable ou de demonstrable
- on privilegie les changements incrementaux, testes, et faciles a relire

## Roadmap Initiale

## Etape 0 - Cadrage Et Stabilisation

Objectif : poser le backlog, clarifier le domaine et fiabiliser l'existant.

- cartographier les flux actuels du CRM
- identifier les trous fonctionnels et les irritants UX
- verifier les parcours critiques et la couverture de tests minimale
- definir les conventions `git` et `GitHub`

Definition of done :

- backlog initial priorise
- conventions d'equipe documentees
- premiers risques techniques identifies

## Etape 1 - CRM Coeur

Objectif : rendre irreprochables les parcours coeur du conseiller.

- comptes
- contacts
- leads
- documents
- navigation dashboard / fiches / formulaires

Definition of done :

- CRUD coeur coherent
- validations metier explicites
- tests sur les parcours les plus sensibles

## Etape 2 - Produits Bancaires

Objectif : mieux gerer l'offre produit et son rattachement aux comptes.

- catalogue produits
- variantes par type de produit
- rattachement a un compte ou a un client
- consultation claire depuis les fiches

Definition of done :

- parcours de creation et edition fiables
- affichage coherent selon le type de produit
- regles metier documentees

## Etape 3 - Portail Client

Objectif : fiabiliser l'experience cote client.

- acces portail
- consultation du dossier
- edition d'informations autorisees
- changement de mot de passe
- premiers parcours securises a lien unique pour des tiers externes
- envois post-validation au client avec resume de dossier et consignes d'acces

Sous-parcours prioritaire retenu :

- gestion de la personne
- gestion des acces
- communication post-validation

Stories regroupees :

- `US009`
- `US012`
- `US015`

Definition of done :

- parcours portail simples et securises
- droits d'acces verifies
- experience lisible sur desktop et mobile

## Etape 4 - Onboarding IA

Objectif : transformer l'onboarding en vrai flux metier exploitable.

- conversation guidee
- collecte progressive des informations
- depot de documents
- revue puis conversion en donnees CRM
- patterns reutilisables pour des parcours externes securises et contextuels

Definition of done :

- flux bout en bout robuste
- gestion claire des etats de session
- conversion vers compte/contact exploitable

## Etape 5 - Industrialisation

Objectif : rendre le produit plus facile a maintenir et a faire evoluer.

- qualite de code
- refactoring cible
- monitoring et logs
- tests supplementaires
- documentation technique

Definition of done :

- dette prioritaire reduite
- commandes et checks de dev clairs
- socle plus sur pour les evolutions suivantes

## Suivi

Statuts utilises dans `docs/backlog.md` :

- `todo`
- `in_progress`
- `blocked`
- `done`

## Decisions En Attente

- priorite business exacte entre CRM coeur, portail client et onboarding IA
- niveau de formalisation GitHub souhaite : simple PR ou vraies issues par US
- niveau d'ambition pour les tests automatiques a court terme
- strategie pour les liens securises externes : token unique, expiration, invalidation et audit
