# Dev Plan

## Objectif

Faire avancer `bank-crm` par increments courts, avec une vision partagee entre produit et technique.

Le perimetre visible aujourd'hui dans l'application couvre deja :

- l'authentification et la gestion des utilisateurs
- les comptes, contacts, leads, documents et produits
- un portail client
- un onboarding conversationnel assiste par IA

Ce plan sert de reference legere. Il sera mis a jour au fil des decisions.

## Cap Produit Mai 2026

Lecture retenue du briefing et du cahier des charges :

- Planilife prepare le rendez-vous professionnel en amont : le client structure sa vie, ses projets et son patrimoine avant de voir un comptable, courtier, notaire, CGP ou banquier prive
- la beta vise un usage avec des cabinets professionnels et leurs clients, pas un simple formulaire autonome
- le dossier client devient un document de confiance collaboratif : client, IA et prescripteur peuvent contribuer, avec provenance et historique
- le dashboard 6 onglets est une piece de demonstration centrale pour la beta
- le rapport beta est universel ; les rapports partenaires specialises sont repousses
- l'acces prescripteur beta est un partage invite par le client ; un compte prescripteur complet est repousse
- les choix de stack, services IA, bibliotheques visuelles ou moteur PDF des documents sources ne sont pas des decisions de planning dans ce fichier

## Principes De Travail

- une seule source de verite pour le plan : `docs/`
- une user story ou un petit lot coherent a la fois
- chaque etape doit livrer quelque chose d'utilisable ou de demonstrable
- on privilegie les changements incrementaux, testes, et faciles a relire
- la provenance des donnees est traitee avant les interfaces riches qui l'affichent
- chaque partage externe part d'un consentement explicite du client

## Roadmap Planilife Beta

## Etape 0 - Recalage Produit Et Mapping Existant

Objectif : aligner le CRM existant avec le vocabulaire Planilife beta.

- mapper compte/contact/onboarding/portail vers client, dossier, entretien, prescripteur et tenant
- isoler ce qui est deja utilisable dans l'application actuelle
- identifier les ecarts majeurs entre le socle CRM livre et le flux Planilife attendu
- confirmer les roles beta : client, prescripteur invite, administrateur

Definition of done :

- vocabulaire produit stabilise dans `docs/`
- epics et US beta ajoutes au backlog
- chemin critique beta partage avec l'equipe

## Etape 1 - Dossier De Confiance Collaboratif

Objectif : poser la source de verite avant de construire le dashboard et le partage.

Stories principales :

- `US030`
- `US031`
- `US032`

Livrables :

- representation metier du dossier client
- provenance des champs sensibles
- historique des modifications
- regles de lecture, edition et correction par role

Definition of done :

- une valeur declaree par le client n'est jamais perdue lors d'une correction
- chaque champ sensible peut afficher sa source
- les corrections futures d'un prescripteur pourront etre expliquees au client

## Etape 2 - Entretien Client En 5 Phases

Objectif : permettre au client de structurer son dossier avant un rendez-vous professionnel.

Stories principales :

- `US023`
- `US024`
- `US025`
- `US026`
- `US027`
- `US028`
- `US029`

Livrables :

- demarrage de l'entretien
- mode conversationnel et mode formulaire sur les memes donnees
- phases identite, projets, risque, timing, patrimoine
- sauvegarde et reprise de session
- syntheses de phase corrigeables

Definition of done :

- un profil standard peut completer l'entretien sans ressaisie
- chaque phase alimente le dossier de confiance
- l'abandon et la reprise conservent l'etat exact du dossier

## Etape 3 - Synthese Client Et Dashboard 6 Onglets

Objectif : rendre visible la valeur de Planilife pour le client et les cabinets beta.

Stories principales :

- `US033`
- `US034`
- `US035`

Livrables :

- score de completude
- 6 onglets : profil, projets, risque, timing, patrimoine, revenus et flux
- edition inline avec provenance
- timeline consolidee et ajustable

Definition of done :

- le client comprend l'etat de son dossier sans relire tout l'entretien
- les modifications du dashboard sont tracees comme les autres contributions
- les donnees manquantes ou a affiner sont visibles sans bloquer l'usage

## Etape 4 - Partage Prescripteur En Beta

Objectif : permettre au client de co-construire son dossier avec un professionnel sans compte prescripteur complet.

Stories principales :

- `US036`
- `US037`
- `US038`

Livrables :

- invitation generee par le client
- choix du role et des blocs partages
- vue prescripteur limitee au perimetre autorise
- correction avec note quand le role l'exige
- notification client apres modification

Definition of done :

- aucun partage ne part sans validation explicite du client
- un prescripteur ne voit que les blocs autorises
- le client peut comprendre ce qui a ete modifie et par qui

## Etape 5 - Rapport Beta Universel

Objectif : livrer une synthese partageable et exploitable pour la beta.

Stories principales :

- `US039`
- `US040`

Livrables :

- rapport unique en 6 sections
- generation possible a partir d'un seuil de completude
- affichage des corrections professionnelles et de leur source
- regeneration apres mise a jour du dossier

Definition of done :

- un dossier beta complet produit un rapport comprehensible sans conseil financier
- les champs enrichis par un prescripteur sont clairement identifies
- les rapports partenaires specialises restent hors beta

## Etape 6 - Gouvernance, Confidentialite Et Back-Office

Objectif : operer la beta sans compromettre la confiance client.

Stories principales :

- `US021`
- `US022`
- `US041`
- `US042`

Livrables :

- parcours compte client et consentement
- droits de consultation, export et effacement
- gestion tenants et utilisateurs
- indicateurs agreges de beta
- suivi des relances, abandons, erreurs et incidents sans lecture des dossiers individuels

Definition of done :

- le client garde le controle de son compte et de ses donnees
- l'administrateur opere la plateforme sans acces au contenu patrimonial
- les incidents et evenements sensibles sont tracables

## Etape 7 - Readiness Beta

Objectif : valider le flux complet avant ouverture aux cabinets pilotes.

Parcours de validation :

- inscription client
- entretien 5 phases
- consultation dashboard
- partage prescripteur
- correction prescripteur
- notification client
- rapport beta universel
- suppression ou export des donnees de test

Definition of done :

- le parcours bout en bout est demonstrable avec un dossier test complet et un dossier partiel
- les principaux risques business sont couverts : abandon, correction externe, donnees manquantes, retrait de consentement
- les cabinets beta savent quoi tester et comment remonter les retours

## Prochain Sprint Recommande

Objectif : derisquer le socle le plus structurant avant de construire le reste.

Lot retenu :

- dossier de confiance collaboratif
- demarrage d'entretien
- phase identite et vie
- premiere synthese avec provenance

Stories candidates pour ce sprint :

- `US030`
- `US031`
- `US032`
- `US023`
- `US024`

Stories optionnelles :

- `US025`
- `US033`
- `US034`

Resultat attendu en fin de sprint :

- un client commence son entretien et renseigne son contexte personnel
- les donnees collectees alimentent deja le dossier source de verite
- la provenance et l'historique minimal sont visibles sur une premiere synthese
- l'equipe confirme que les choix de planning restent independants des propositions techno du cahier des charges

## Suivi

Statuts utilises dans `docs/backlog.md` :

- `todo`
- `in_progress`
- `blocked`
- `done`

## Decisions En Attente

- mapping definitif entre les entites CRM existantes et le vocabulaire Planilife : client, dossier, prescripteur, tenant
- liste exacte des champs sensibles qui portent une provenance des la beta
- libelles publics des sources : declare, detecte par IA, mis a jour, corrige par prescripteur
- roles beta a activer au lancement : comptable, courtier, notaire, CGP, banquier prive
- blocs visibles et corrigibles par role dans le partage prescripteur
- seuil de completude exact pour activer le rapport beta
- contenu final du rapport universel beta et mentions legales a afficher
- niveau de prise en charge du droit d'export dans la beta par rapport a V1
