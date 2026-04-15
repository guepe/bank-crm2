## Migration UserBundle -> Symfony 8

Date de cadrage: 15 avril 2026

Sources officielles verifiees:
- Symfony 8.0 est la version stable courante; la release `8.0.8` a ete publiee le 31 mars 2026.
- Symfony 7.4 est la version LTS courante.

Comme tu as demande la "derniere version", cette premiere tranche cible un code compatible Symfony 8.

### Point de depart

L'ancien `GuepeUserBundle` repose presque entierement sur FOSUserBundle:
- `Symfony/src/Guepe/UserBundle/GuepeUserBundle.php`
- `Symfony/src/Guepe/UserBundle/Entity/User.php`
- `Symfony/src/Guepe/UserBundle/Resources/config/routing.yml`
- `Symfony/src/Guepe/CrmBankBundle/Resources/config/security.yml`

Le portage moderne remplace FOSUserBundle par:
- une entite `User` native Symfony;
- un repository Doctrine;
- la configuration `security.yaml`;
- un authenticator pour le login;
- des controlleurs et templates simples.

### MVC: decoupage retenu

1. Modele
   Entite `User`, persistence Doctrine, roles, mot de passe hash.
2. Controleur
   Login, logout, profil, changement de mot de passe.
3. Vue
   Formulaire de login, ecran profil, messages d'erreur.

### Ce qui est fait dans cette etape

Cette etape pose la couche Modele moderne dans `migration/symfony8-user/`:
- `src/Entity/User.php`
- `src/Repository/UserRepository.php`
- `config/packages/security.yaml`

### Ce qui est fait dans l'etape 2

La couche Controleur et la vue de login sont maintenant posees:
- `src/Controller/SecurityController.php`
- `src/Controller/ProfileController.php`
- `src/Security/LoginFormAuthenticator.php`
- `templates/security/login.html.twig`
- `templates/profile/show.html.twig`
- `config/routes/security.yaml`

Le code est volontairement isole de l'application legacy pour pouvoir migrer par morceaux sans casser l'existant.

### Mapping ancien -> nouveau

Ancien:
- bundle herite de `FOSUserBundle`;
- `BaseUser`;
- encodeur `plaintext`;
- login par `username`.

Nouveau:
- entite `App\Entity\User`;
- interfaces `UserInterface` et `PasswordAuthenticatedUserInterface`;
- `password_hashers`;
- login conserve sur `username` pour limiter la casse fonctionnelle.

### Notes de migration donnees

L'ancienne table `user` issue de FOSUserBundle contient probablement des colonnes legacy (`username_canonical`, `email_canonical`, `salt`, `locked`, etc.).
Deux strategies sont possibles:

1. Transition douce
   Garder la table existante, ignorer les colonnes legacy non lues par la nouvelle entite, puis convertir les donnees a froid.
2. Refonte propre
   Creer une nouvelle table `user`, migrer les comptes utiles, supprimer ensuite l'ancien schema.

Pour un vieux projet comme celui-ci, je recommande souvent la transition douce au debut, puis nettoyage apres validation.

### Prochaine etape conseillee

Passer a la suite de la migration du module utilisateur:
- brancher ces fichiers dans une vraie arborescence Symfony 8;
- generer la migration Doctrine correspondant a l'entite `User`;
- ecrire une commande de migration des anciens comptes FOSUserBundle;
- ajouter les formulaires de changement de mot de passe et d'edition du profil.
