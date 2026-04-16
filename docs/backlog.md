# Backlog

## Mode D'Emploi

- chaque US a un identifiant stable
- on garde des stories petites, testables et demonstrables
- on peut enrichir les criteres d'acceptation avant implementation

Colonnes recommandees :

- `prio` : `P0`, `P1`, `P2`
- `statut` : `todo`, `in_progress`, `blocked`, `done`

## Stories Prioritaires

| ID | Epic | User story | Prio | Statut |
| --- | --- | --- | --- | --- |
| US001 | Dashboard | En tant que conseiller, je peux acceder a un tableau de bord utile pour voir les points d'entree du CRM. | P0 | done |
| US002 | Comptes | En tant que conseiller, je peux creer un compte afin de commencer le suivi d'un client. | P0 | done |
| US003 | Comptes | En tant que conseiller, je peux consulter la fiche d'un compte afin de voir ses informations principales. | P0 | done |
| US004 | Contacts | En tant que conseiller, je peux ajouter un contact a un compte afin de suivre les interlocuteurs associes. | P0 | done |
| US005 | Leads | En tant que conseiller, je peux creer et suivre un lead afin de piloter une opportunite commerciale. | P0 | done |
| US006 | Documents | En tant que conseiller, je peux deposer et telecharger des documents afin de centraliser les pieces d'un dossier. | P0 | done |
| US007 | Produits | En tant que conseiller, je peux rattacher un produit bancaire a un compte afin de suivre l'equipement client. | P1 | done |
| US008 | Utilisateurs | En tant qu'administrateur, je peux creer un utilisateur et lui attribuer un role afin de gerer l'acces a l'application. | P1 | done |
| US009 | Portail | En tant que client, je peux acceder a mon portail afin de consulter et mettre a jour les informations autorisees. | P1 | done |
| US010 | Onboarding | En tant que client, je peux demarrer un onboarding conversationnel afin de transmettre mon dossier progressivement. | P1 | done |
| US011 | Onboarding | En tant que conseiller, je peux revoir puis convertir une session d'onboarding en donnees CRM afin d'eviter une ressaisie manuelle. | P1 | done |
| US012 | Securite | En tant qu'utilisateur, je peux me connecter et gerer mon mot de passe afin d'acceder de facon securisee a mon espace. | P0 | done |
| US013 | Contacts | En tant que conseiller, je peux associer une banque a une personne avec un interlocuteur bancaire specifique afin de rattacher le bon contexte bancaire a son dossier CRM. | P1 | done |
| US014 | Banque | En tant que conseiller, je peux envoyer le dossier d'un client a une banque via un lien securise et unique afin que son interlocuteur bancaire complete les produits detenus par ce client dans cette banque. | P1 | done |
| US015 | Portail | En tant que conseiller, je peux envoyer a la personne un lien securise a son adresse e-mail apres validation du dossier afin qu'elle consulte le resume de ses donnees et, si besoin, les informations de mot de passe. | P1 | done |
| US016 | Recherche | En tant que conseiller, je peux rechercher rapidement un compte, un contact ou un lead afin d'acceder sans friction au bon dossier. | P0 | todo |
| US017 | Recherche | En tant que conseiller, je peux filtrer les listes de comptes, contacts et leads afin de reduire le bruit et travailler sur un sous-ensemble pertinent. | P0 | todo |
| US018 | Leads | En tant que conseiller, je peux visualiser et modifier le statut d'un lead afin de mieux suivre sa progression commerciale. | P1 | done |
| US019 | Historique | En tant que conseiller, je peux consulter une timeline simple des actions importantes d'un dossier afin de comprendre rapidement ce qui s'est passe. | P1 | done |
| US020 | Qualite | En tant qu'equipe produit, nous pouvons executer facilement les checks et tests critiques afin de securiser les evolutions du CRM. | P0 | todo |

## Details Des Premieres US

## US002 - Creer Un Compte

En tant que conseiller
Je veux creer un compte
Afin de commencer le suivi d'un client

Criteres d'acceptation :

- un formulaire permet de saisir les informations minimales du compte
- les champs obligatoires sont valides cote serveur
- apres creation, l'utilisateur est redirige vers la fiche du compte

## US004 - Ajouter Un Contact

En tant que conseiller
Je veux ajouter un contact a un compte
Afin de suivre les interlocuteurs associes

Criteres d'acceptation :

- un contact peut etre cree depuis le contexte d'un compte ou independamment
- la relation avec le compte est visible sur la fiche
- les erreurs de validation sont affichees clairement

## US006 - Gerer Les Documents

En tant que conseiller
Je veux deposer et retrouver des documents
Afin de centraliser les pieces d'un dossier

Criteres d'acceptation :

- un document peut etre televerse avec ses metadonnees
- un document peut etre consulte et telecharge
- les acces non autorises sont refuses

## US010 - Demarrer Un Onboarding

En tant que client
Je veux demarrer un onboarding conversationnel
Afin de transmettre mon dossier progressivement

Criteres d'acceptation :

- une session peut etre creee et reprise
- le chat conserve l'historique des echanges
- des documents peuvent etre ajoutes au parcours

Hypothese d'implementation retenue :

- une session appartient a un utilisateur authentifie
- le parcours conversationnel est accessible au client sur plusieurs reprises
- les pieces jointes du chat sont stockees avec le dossier et visibles dans l'historique

## US011 - Revoir Et Convertir Une Session D'Onboarding

En tant que conseiller
Je veux revoir puis convertir une session d'onboarding en donnees CRM
Afin d'eviter une ressaisie manuelle

Criteres d'acceptation :

- un utilisateur interne peut consulter les sessions onboarding existantes
- il peut relire la synthese du dossier et l'historique de conversation
- il peut finaliser la session et creer le contact et le compte CRM associes
- la session est marquee comme finalisee apres conversion

Hypothese d'implementation retenue :

- les utilisateurs internes peuvent acceder a l'index, a la relecture et a la conversion des sessions
- la conversion reutilise les mecanismes de consolidation deja presents dans le service d'onboarding

## US013 - Associer Une Banque Et Son Interlocuteur A Une Personne

En tant que conseiller
Je veux associer une banque a une personne avec un interlocuteur bancaire specifique
Afin de rattacher le bon contexte bancaire a son dossier CRM

Criteres d'acceptation :

- une banque peut etre renseignee depuis la creation ou l'edition d'une personne ou d'un contact
- pour une meme banque, l'interlocuteur bancaire associe peut etre different selon la personne suivie
- la banque et l'interlocuteur associes sont visibles sur la fiche de la personne ou du contact
- la banque et l'interlocuteur restent modifiables sans perdre les autres informations du dossier
- si aucune banque n'est definie, le comportement de l'application reste coherent

Point a clarifier avant implementation :

- banque en texte libre ou selection dans un referentiel de banques
- l'interlocuteur bancaire est-il un simple libelle, un contact dedie, ou une vraie entite relationnelle

Hypothese d'implementation retenue :

- la relation banque / interlocuteur est geree comme une entite dediee rattachee au contact
- elle stocke le nom de la banque, le nom de l'interlocuteur, son e-mail, son telephone et des notes

## US014 - Envoyer Un Dossier Client A Une Banque Via Un Lien Securise

En tant que conseiller
Je veux envoyer le dossier d'un client a une banque via un lien securise et unique
Afin que son interlocuteur bancaire complete les produits detenus par ce client dans cette banque

Criteres d'acceptation :

- depuis le dossier d'un client, un conseiller peut generer un envoi vers une banque et son contact associe
- l'envoi produit un lien unique, difficile a deviner, associe a un client et a un contexte bancaire precis
- le lien permet a l'interlocuteur bancaire d'acceder uniquement au formulaire ou parcours prevu pour ce client et cette banque
- l'interlocuteur bancaire peut renseigner ou completer les produits detenus par le client dans cette banque
- les donnees soumises via ce lien sont rattachees au bon client et au bon contexte bancaire
- le lien peut etre invalide apres usage, expiration, ou fermeture du dossier selon la regle retenue
- l'application conserve une trace de l'envoi et de la reponse recue

Dependances et liens :

- depend de la clarification de `US013` sur le couple banque / interlocuteur
- pourra reutiliser des mecanismes proches du portail ou de l'onboarding pour l'acces securise

Points a clarifier avant implementation :

- le lien est-il strictement a usage unique ou reutilisable jusqu'a expiration
- l'interlocuteur bancaire doit-il seulement completer des produits ou aussi voir une partie du dossier client
- l'envoi se fait-il par email depuis l'application ou le lien est-il seulement genere puis copie
- faut-il une validation finale cote conseiller avant integration definitive dans le dossier client

Hypothese d'implementation retenue :

- le lien bancaire est unique, securise et reutilisable jusqu'a expiration
- l'interlocuteur bancaire voit le contexte client utile et peut ajouter des produits bancaires un par un
- les produits ajoutes sont integres directement dans le dossier client sur un compte existant, avec la banque comme societe source

## US015 - Envoyer A La Personne Un Lien De Consultation Apres Validation

En tant que conseiller
Je veux envoyer a la personne un lien securise a son adresse e-mail apres validation du dossier
Afin qu'elle consulte le resume de ses donnees et, si besoin, les informations de mot de passe

Criteres d'acceptation :

- quand le dossier est marque comme valide par le conseiller, un envoi a la personne peut etre prepare ou declenche
- l'envoi est adresse a l'adresse e-mail de la bonne personne
- l'envoi contient un lien securise associe a la bonne personne et a son dossier
- via ce lien, la personne peut consulter un resume des donnees fournies dans son dossier
- l'envoi peut inclure, selon la regle retenue, un guide de connexion ou de gestion du mot de passe
- l'acces est limite au bon destinataire, avec une duree de validite ou une politique d'invalidation definie
- l'application conserve une trace de l'envoi realise

Dependances et liens :

- s'appuie sur les donnees du dossier compte / contact une fois validees
- peut reutiliser des mecanismes existants du portail client et des liens securises

Points a clarifier avant implementation :

- la personne consulte-t-elle uniquement un resume en lecture seule ou peut-elle encore corriger certaines donnees
- le lien ouvre-t-il une page publique securisee ou un acces guide vers le portail client
- le guide mot de passe parle-t-il d'un mot de passe existant, d'une creation initiale, ou d'une reinitialisation
- l'envoi est-il automatique a la validation ou manuel depuis le conseiller

Lot fonctionnel retenu :

- `US009`, `US012` et `US015` sont traites ensemble car ils couvrent un meme parcours de gestion de la personne et de ses acces

Hypothese d'implementation retenue :

- faute de statut de validation de dossier existant dans le modele, l'envoi est declenche manuellement par un administrateur depuis la fiche contact

## US016 - Rechercher Un Compte, Un Contact Ou Un Lead

En tant que conseiller
Je veux rechercher rapidement un compte, un contact ou un lead
Afin d'acceder sans friction au bon dossier

Criteres d'acceptation :

- un champ de recherche est disponible sur les vues listes principales
- la recherche fonctionne au minimum sur les champs les plus utiles de chaque objet
- les resultats affiches correspondent au terme saisi sans comportement surprenant
- en absence de resultat, l'interface affiche un etat vide clair

Hypothese d'implementation retenue :

- la recherche est d'abord textuelle avec un comportement simple et explicable
- le perimetre initial couvre comptes, contacts et leads

## US017 - Filtrer Les Listes Principales

En tant que conseiller
Je veux filtrer les listes de comptes, contacts et leads
Afin de reduire le bruit et travailler sur un sous-ensemble pertinent

Criteres d'acceptation :

- chaque liste propose un petit ensemble de filtres utiles et comprehensibles
- les filtres actifs sont visibles et faciles a reinitialiser
- le resultat de la liste se met a jour de facon fiable selon les filtres selectionnes
- la combinaison recherche + filtres reste coherente

Point a clarifier avant implementation :

- quels filtres sont prioritaires par objet dans la V1

Hypothese d'implementation retenue :

- on commence avec quelques filtres a forte valeur plutot qu'un moteur generique

## US018 - Suivre Le Statut D'Un Lead

En tant que conseiller
Je veux visualiser et modifier le statut d'un lead
Afin de mieux suivre sa progression commerciale

Criteres d'acceptation :

- un lead affiche un statut metier lisible
- le statut peut etre modifie depuis la fiche et, si pertinent, depuis la liste
- les statuts disponibles sont bornes et documentes
- les listes et recherches peuvent exploiter ce statut

Point a clarifier avant implementation :

- liste cible des statuts et regles de transition a appliquer ou non

Hypothese d'implementation retenue :

- la V1 privilegie un workflow simple sans automatisme complexe

## US019 - Consulter Une Timeline D'Activite

En tant que conseiller
Je veux consulter une timeline simple des actions importantes d'un dossier
Afin de comprendre rapidement ce qui s'est passe

Criteres d'acceptation :

- une fiche affiche les evenements principaux lies au dossier
- les evenements sont presentes dans l'ordre chronologique inverse
- chaque entree de timeline expose au minimum le type d'action, la date et un contexte lisible
- en absence d'historique, un etat vide clair est affiche

Dependances et liens :

- gagne en valeur si les actions de creation, edition et envoi sont deja tracees de facon fiable

Hypothese d'implementation retenue :

- la V1 peut se limiter a quelques evenements structurants plutot qu'a une historisation exhaustive

## US020 - Securiser Les Checks Et Tests Critiques

En tant qu'equipe produit
Nous pouvons executer facilement les checks et tests critiques
Afin de securiser les evolutions du CRM

Criteres d'acceptation :

- la documentation de dev liste clairement les commandes utiles au quotidien
- un socle minimal de checks peut etre lance localement avant merge
- les parcours critiques identifies sont couverts par des tests automatises ou des verifications explicites
- les commandes en echec remontent un signal exploitable par l'equipe

Hypothese d'implementation retenue :

- on cible d'abord les parcours coeur deja livres plutot qu'une couverture exhaustive

## Sprint Suivant Propose

Lot recommande :

- `US016`
- `US017`
- `US020`

Lot optionnel si la capacite le permet :

- `US018`
- `US019`

Objectif du sprint :

- ameliorer la productivite immediate sur les listes CRM
- reduire le risque sur les prochaines livraisons

Definition of done du sprint :

- recherche et filtres livrables sur le perimetre coeur retenu
- checks de dev et tests critiques clarifies
- au moins un sujet de pilotage commercial ou de visibilite dossier lance en complement si la capacite reste disponible

## Idees De Backlog A Affiner

- pipeline leads plus riche que le simple statut
- notifications ou relances internes
- historisation des changements critiques au-dela de la timeline V1
- tableaux de bord metier
