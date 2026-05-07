# Backlog

## Mode D'Emploi

- chaque US a un identifiant stable
- on garde des stories petites, testables et demonstrables
- on peut enrichir les criteres d'acceptation avant implementation

Colonnes recommandees :

- `prio` : `P0`, `P1`, `P2`
- `statut` : `todo`, `in_progress`, `blocked`, `done`

## Lecture Business Planilife Mai 2026

Les epics ci-dessous reprennent le briefing et le cahier des charges en privilegiant le flux business. Les choix de stack, de bibliotheques, d'outils PDF ou de services IA ne pilotent pas ce backlog.

| Epic | Flux business | Resultat attendu beta |
| --- | --- | --- |
| EP01 | Confiance, compte et consentement | Le client peut entrer dans Planilife, comprendre le traitement de ses donnees, gerer son compte et garder le controle. |
| EP02 | Entretien life planning en 5 phases | Le client structure sa situation de vie, ses projets, ses valeurs, son timing et son patrimoine avant le rendez-vous professionnel. |
| EP03 | Dossier de confiance collaboratif | Le dossier n'est pas un formulaire ponctuel : chaque donnee sensible garde sa source, son historique et son niveau de confiance. |
| EP04 | Synthese client et dashboard 6 onglets | Le client visualise son dossier, son score de completude, ses objectifs, sa timeline, son patrimoine et ses flux. |
| EP05 | Partage prescripteur et correction encadree | Le client partage explicitement des blocs de dossier avec un professionnel qui peut lire ou corriger selon son role. |
| EP06 | Rapport beta universel | Le client obtient un rapport unique exploitable en beta, avec les sources des champs corriges par un professionnel. |
| EP07 | Gouvernance, confidentialite et audit | Les donnees sensibles sont protegees, les actions importantes sont tracables et les droits RGPD sont actionnables. |
| EP08 | Pilotage beta et back-office | L'equipe peut piloter tenants, utilisateurs, qualite des entretiens, relances et incidents sans acceder au contenu des dossiers. |

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
| US016 | Recherche | En tant que conseiller, je peux rechercher rapidement un compte, un contact ou un lead afin d'acceder sans friction au bon dossier. | P0 | done |
| US017 | Recherche | En tant que conseiller, je peux filtrer les listes de comptes, contacts et leads afin de reduire le bruit et travailler sur un sous-ensemble pertinent. | P0 | done |
| US018 | Leads | En tant que conseiller, je peux visualiser et modifier le statut d'un lead afin de mieux suivre sa progression commerciale. | P1 | done |
| US019 | Historique | En tant que conseiller, je peux consulter une timeline simple des actions importantes d'un dossier afin de comprendre rapidement ce qui s'est passe. | P1 | done |
| US020 | Qualite | En tant qu'equipe produit, nous pouvons executer facilement les checks et tests critiques afin de securiser les evolutions du CRM. | P0 | done |
| US021 | EP01 Confiance | En tant que client, je peux creer mon compte, verifier mon email, me connecter et gerer mon mot de passe afin d'acceder a mon espace Planilife de facon fiable. | P0 | done |
| US022 | EP01 Confiance | En tant que client, je peux comprendre et valider les usages de mes donnees avant de commencer afin de garder le controle sur mon dossier. | P0 | done |
| US023 | EP02 Entretien | En tant que client, je peux demarrer l'entretien en mode conversationnel ou formulaire et basculer entre les deux sans perte de donnees afin de choisir l'experience qui me convient. | P0 | in_progress |
| US024 | EP02 Entretien | En tant que client, je peux completer la phase identite et vie afin de poser mon contexte personnel, familial et professionnel. | P0 | in_progress |
| US025 | EP02 Entretien | En tant que client, je peux formuler mes projets et priorites afin que mon dossier reflete mes objectifs de vie. | P0 | in_progress |
| US026 | EP02 Entretien | En tant que client, je peux exprimer mon rapport au risque, mes valeurs et mes intentions de transmission afin de cadrer les arbitrages futurs. | P0 | in_progress |
| US027 | EP02 Entretien | En tant que client, je peux ordonner mes etapes de vie et leurs horizons afin de construire une timeline exploitable. | P0 | in_progress |
| US028 | EP02 Entretien | En tant que client, je peux estimer mon patrimoine, mes credits, mes revenus et mes flux par fourchettes afin de preparer un rendez-vous sans devoir fournir des montants exacts. | P0 | in_progress |
| US029 | EP02 Entretien | En tant que client, je peux quitter et reprendre l'entretien exactement ou je l'avais laisse afin de completer mon dossier en plusieurs fois. | P0 | done |
| US030 | EP03 Dossier | En tant qu'equipe produit, nous pouvons representer le dossier comme source de verite collaborative afin que les donnees declarees, detectees, modifiees et corrigees coexistent sans ecrasement. | P0 | done |
| US031 | EP03 Dossier | En tant que client, je peux voir la provenance d'un champ sensible afin de distinguer ce que j'ai declare, ce qui a ete detecte et ce qui a ete corrige. | P0 | todo |
| US032 | EP03 Dossier | En tant qu'equipe produit, nous pouvons tracer chaque modification de champ afin d'expliquer qui a change quoi, quand, et pourquoi. | P0 | in_progress |
| US033 | EP04 Dashboard | En tant que client, je peux consulter un dashboard en 6 onglets afin de comprendre mon profil, mes projets, mes valeurs, mon timing, mon patrimoine et mes flux. | P0 | done |
| US034 | EP04 Dashboard | En tant que client, je peux modifier mes donnees directement dans le dashboard afin de corriger mon dossier sans relancer tout l'entretien. | P0 | done |
| US035 | EP04 Dashboard | En tant que client, je peux visualiser et ajuster les evenements de ma timeline afin de fiabiliser les etapes cles de mon plan de vie. | P1 | done |
| US036 | EP05 Partage | En tant que client, je peux generer une invitation pour un prescripteur avec un role et des blocs autorises afin de partager seulement ce que j'ai valide. | P0 | in_progress |
| US037 | EP05 Partage | En tant que prescripteur, je peux consulter uniquement les blocs autorises et corriger les champs relevant de mon role afin d'enrichir le dossier client. | P0 | in_progress |
| US038 | EP05 Partage | En tant que client, je peux etre notifie des modifications faites par un prescripteur et les retrouver dans l'historique afin de conserver la maitrise du dossier. | P1 | in_progress |
| US039 | EP06 Rapport | En tant que client, je peux generer un rapport beta universel lorsque mon dossier est suffisamment complet afin de disposer d'une synthese partageable. | P0 | todo |
| US040 | EP06 Rapport | En tant que client ou prescripteur autorise, je peux voir dans le rapport les sources des champs corriges afin de comprendre la provenance des informations importantes. | P1 | todo |
| US041 | EP07 Gouvernance | En tant que client, je peux consulter, exporter ou demander la suppression de mes donnees afin d'exercer mes droits RGPD. | P1 | done |
| US042 | EP08 Pilotage | En tant qu'administrateur, je peux piloter tenants, utilisateurs, relances et indicateurs agreges sans lire les dossiers individuels afin d'operer la beta en confiance. | P1 | in_progress |

## Audit Code Rapide - 2026-05-06

Statuts recalibres apres lecture du code Symfony existant.

| US | Statut retenu | Constat code |
| --- | --- | --- |
| US016 | done | Recherche texte presente sur comptes, contacts et leads, avec tests d'integration dedies. |
| US017 | done | Filtres par objet presents sur les trois listes CRM, avec etats vides et tests. |
| US020 | done | `composer check`, lint Twig, lint container, tests integration et CI GitHub Actions sont documentes/configures. |
| US021 | done | Inscription, verification e-mail explicite, login, portail client, activation par lien, changement et reset autonome de mot de passe sont couverts. |
| US022 | done | Un ecran de consentement bloque l'onboarding tant que le client n'a pas valide les usages IA, rapport et partage. |
| US023 | in_progress | Entretien conversationnel demarrable et persiste. Le mode formulaire et le toggle conversation/formulaire ne sont pas presents. |
| US024 | in_progress | Phase `discovery` existante avec prenom, age, situation familiale et pro. Enfants et attente principale restent a cadrer/forcer. |
| US025 | in_progress | Phase `qualification` existante pour vision, retraite et objectifs. Le ranked choice produit n'est pas encore une experience dediee. |
| US026 | in_progress | Phase `risk_analysis` existante pour profil risque et transmission. La profondeur "valeurs" reste a enrichir. |
| US027 | in_progress | Phase `etapes` existante. Il manque le registre timeline structure exploitable comme dossier de vie. |
| US028 | in_progress | Phase `patrimoine` et extraction de produits existent. La couverture exacte des blocs patrimoine/flux reste incomplete. |
| US029 | done | Une session en cours est retrouvee et reprise, avec messages, phase et donnees persistees. |
| US030 | done | Le dossier source conserve la valeur courante dans `OnboardingSession.extractedData` et l'historique collaboratif dans `field_edit`, avec sources declaree, detectee, mise a jour et corrigee. |
| US031 | todo | Aucun badge ou champ de provenance visible par valeur sensible. |
| US032 | in_progress | Le journal `field_edit` trace les changements issus de l'onboarding et du service de mise a jour de champ. Il reste a brancher les futures interfaces inline/prescripteur sur ce point d'entree. |
| US033 | done | Le portail client expose maintenant un dashboard Planilife en 6 onglets avec score de completude, sources et synthese dossier. |
| US034 | done | Les champs du dashboard sont editables inline et passent par `OnboardingService::updateSessionField`, avec provenance et historique `field_edit`. |
| US035 | done | La timeline de vie est visualisee et ajustable depuis le dashboard, puis synchronisee avec `etapes.timeline` et `etapes.etapes`. |
| US036 | in_progress | Des liens securises existent pour portail et banque, mais le client ne genere pas encore une invitation par role et blocs autorises. |
| US037 | in_progress | Un interlocuteur bancaire peut completer des produits via lien. La matrice multi-roles prescripteur reste a faire. |
| US038 | in_progress | L'historique interne trace l'envoi/retour banque. La notification client et l'historique client des corrections restent a faire. |
| US039 | todo | Pas de generation de rapport beta universel. |
| US040 | todo | Pas de rapport, donc pas d'affichage des sources dans le rapport. |
| US041 | done | Le portail expose les donnees principales, fournit un export JSON et enregistre une demande de suppression a traiter sous 48h. |
| US042 | in_progress | Gestion utilisateurs et dashboard de compteurs existent. Pas encore tenants, relances, indicateurs beta ni separation admin/dossiers au niveau attendu. |

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

## Details Des Epics Planilife Beta

## EP01 - Confiance, Compte Et Consentement

US couvertes : `US021`, `US022`, `US041`

Criteres d'acceptation business :

- le client peut creer, verifier, recuperer et supprimer son acces sans intervention interne
- le consentement de traitement des donnees est explicite avant l'entretien
- le client comprend ce qui est utilise pour l'IA, le rapport et le partage prescripteur
- les droits de consultation, export et effacement sont visibles depuis l'espace client

## EP02 - Entretien Life Planning En 5 Phases

US couvertes : `US023` a `US029`

Criteres d'acceptation business :

- l'entretien couvre identite et vie, projets, risque et valeurs, timing, patrimoine et flux
- le mode conversationnel et le mode formulaire manipulent les memes donnees
- chaque phase produit une synthese utile et corrigeable par le client
- les reponses sont sauvegardees au fil de l'eau et la reprise se fait au bon endroit
- le parcours reste utilisable sur mobile, car une partie de la beta sera faite hors bureau

## EP03 - Dossier De Confiance Collaboratif

US couvertes : `US030`, `US031`, `US032`

Criteres d'acceptation business :

- le dossier conserve les valeurs initiales et les valeurs courantes sans ecraser l'historique
- les sources visibles sont au minimum : declare par le client, detecte par IA, mis a jour par le client, corrige par un prescripteur
- toute correction sensible laisse une trace lisible pour le client et l'equipe produit
- ce socle est traite avant les interfaces riches, car il conditionne le partage et le rapport

Etat code :

- `US030` done : `OnboardingSession.extractedData` reste la valeur courante exploitable par le CRM, et `field_edit` conserve les valeurs precedentes, la source, le role, l'auteur, la raison et l'horodatage.
- `US031` todo : pas encore d'affichage client des badges ou details de provenance par champ.
- `US032` in_progress : le journal technique existe et couvre le flux onboarding, mais les futures editions inline/prescripteur doivent utiliser le meme point d'entree.

## EP04 - Synthese Client Et Dashboard 6 Onglets

US couvertes : `US033`, `US034`, `US035`

Criteres d'acceptation business :

- le dashboard donne une vue complete du dossier apres ou pendant l'entretien
- les 6 onglets couvrent profil de vie, projets, risque et valeurs, timing, patrimoine, revenus et flux
- le score de completude indique si le dossier peut produire un rapport utile
- l'edition inline met a jour le dossier avec provenance et historique
- la timeline consolide les evenements de vie, professionnels, familiaux, patrimoniaux et financiers

## EP05 - Partage Prescripteur Et Correction Encadree

US couvertes : `US036`, `US037`, `US038`

Criteres d'acceptation business :

- le partage part toujours du client, avec validation des blocs visibles par le prescripteur
- le role du prescripteur determine ce qu'il peut lire, modifier ou corriger avec note
- le prescripteur n'a pas besoin d'un compte complet en beta
- le client peut suivre les consultations, corrections et notifications liees a ses invitations

## EP06 - Rapport Beta Universel

US couvertes : `US039`, `US040`

Criteres d'acceptation business :

- un rapport universel suffit pour la beta et remplace les rapports partenaires specialises
- le rapport couvre introduction, profil, objectifs, risque et valeurs, feuille de route, patrimoine et flux
- le rapport mentionne les corrections professionnelles quand elles existent
- le rapport se regenere apres mise a jour du dossier

## EP07 - Gouvernance, Confidentialite Et Audit

US couvertes : `US041`

Criteres d'acceptation business :

- les donnees patrimoniales sensibles ne sont accessibles qu'aux acteurs autorises
- les droits RGPD sont actionnables et testables
- les evenements de securite, acces et partage sont auditables
- les messages utilisateur restent comprehensibles sans exposer de details internes

## EP08 - Pilotage Beta Et Back-Office

US couvertes : `US042`

Criteres d'acceptation business :

- l'administrateur gere les tenants, plans, utilisateurs et suspensions
- l'administrateur voit des indicateurs agreges utiles a la beta, pas les dossiers individuels
- l'equipe peut suivre les abandons, relances, taux de completude et qualite des extractions
- les incidents et corrections de parcours alimentent une boucle d'amelioration avant V1

## Sprint Suivant Propose

Lot recommande :

- `US031`
- `US032`
- `US023`
- `US024`

Lot optionnel si la capacite le permet :

- `US025`
- `US026`
- `US036`

Objectif du sprint :

- poser le socle du dossier de confiance avant de produire des interfaces avancees
- livrer une tranche verticale simple : demarrage d'entretien, phase identite et vie, dossier structure, premiers badges de provenance
- verifier que le mode conversationnel et le mode formulaire alimentent la meme source de verite

Definition of done du sprint :

- un client peut commencer l'entretien et sauvegarder les champs de la phase 1
- les champs sensibles du dossier disposent d'une provenance et d'un historique minimal
- une premiere vue de synthese montre les donnees collectees et leur source
- les choix techno proposes dans les documents sources restent hors decision de backlog

## Idees De Backlog A Affiner

- rapports partenaires specialises par type de professionnel
- compte complet prescripteur / interface cabinet
- lien partenaire permanent apres beta
- upload de documents justificatifs
- export RGPD JSON complet si non retenu dans la premiere beta
- paiement B2C Premium
- simulations V2 : analyse probabiliste, scenarios, calculs detailles de pension
