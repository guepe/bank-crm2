# Working Agreement

## Comment On Travaille Ensemble

- on discute du besoin ici
- je mets a jour `docs/dev-plan.md` et `docs/backlog.md` quand on arbitre
- on implemente ensuite une US ou un petit lot coherent
- a la fin de chaque lot, on resume ce qui a ete fait et ce qu'on prend ensuite

## Conventions Git

Nom de branche recommande :

- `feature/US002-create-account`
- `feature/US010-onboarding-chat`
- `fix/US006-document-download`
- `chore/tests-onboarding`

Convention de commit simple :

- `feat(accounts): add account creation flow`
- `fix(documents): secure file download`
- `test(onboarding): cover session review flow`
- `docs(backlog): prioritize onboarding stories`

## Conventions GitHub

Option legere :

- le backlog principal reste dans `docs/backlog.md`
- une PR pour chaque sujet important
- le titre de PR reprend l'identifiant de story quand il existe

Exemples :

- `[US002] Add account creation flow`
- `[US006] Secure document upload and download`

Option plus structuree :

- `1 issue GitHub = 1 user story`
- `1 branche = 1 issue ou 1 story`
- `1 PR = 1 lot relisible`

## Cycle Court Recommande

1. on choisit la prochaine US
2. on precise les criteres d'acceptation si besoin
3. j'implemente le changement
4. je lance les checks utiles
5. on ajuste le backlog et on passe a la suite

## Quand On Ouvre Une Discussion

On s'arrete pour decider ensemble si :

- un choix impacte fortement l'architecture
- une story est trop grosse et doit etre decoupee
- une regression ou un conflit avec l'existant apparait
- il faut choisir entre vitesse de livraison et dette technique
