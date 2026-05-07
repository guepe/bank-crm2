# Scénarios de test Planilife — Beta Mai 2026

Scénarios manuels exécutables dans un navigateur. Chaque scénario est indépendant et liste ses prérequis.

---

## S01 — Inscription et premier accès client

**Prérequis :** aucun compte existant pour l'email utilisé.

1. Aller sur `/register`
2. Saisir un nom d'utilisateur, un email valide et un mot de passe
3. Valider — un email de vérification est envoyé (ou le lien s'affiche en console si BREVO désactivé)
4. Cliquer le lien de vérification → redirection vers `/login`
5. Se connecter avec les identifiants créés
6. Un écran de consentement s'affiche — lire et accepter
7. Résultat attendu : arrivée sur le dashboard `/portal` avec score de complétude à 0 %

---

## S02 — Entretien en mode conversationnel (5 phases)

**Prérequis :** compte client connecté, consentement accepté.

1. Cliquer "Reprendre l'entretien" depuis le dashboard
2. **Phase Discovery** : écrire dans le chat "Je m'appelle Marie, j'ai 42 ans, mariée, architecte, j'aimerais préparer ma retraite"
   - Vérifier que les champs prenom, age, statut, pro, attente apparaissent dans le panneau "Données extraites"
   - Vérifier que les champs manquants se réduisent à mesure que l'IA extrait les données
3. Répondre aux questions suivantes jusqu'à ce que la barre de progression atteigne 100 % pour la phase
4. **Phase Qualification** : mentionner vision patrimoniale, âge de retraite, objectifs et indiquer les priorités en ordre
5. **Phase Risque** : exprimer un profil de risque modéré, les convictions d'investissement (ex. ESG), les intentions de transmission
6. **Phase Étapes** : lister 2-3 événements de vie avec leur horizon temporel
   - Vérifier que la section "Flux" du panneau de données affiche `etapes.timeline` structuré
7. **Phase Patrimoine** : indiquer l'immobilier, la trésorerie, les placements, les revenus et charges mensuels
8. Résultat attendu :
   - La barre de progression global dépasse 80 %
   - Le bouton "Voir mon rapport" apparaît dans le dashboard

---

## S03 — Mode formulaire (toggle)

**Prérequis :** session d'entretien en cours.

1. Dans la page de chat, cliquer le bouton "Mode formulaire"
2. Un panneau de saisie directe apparaît avec les champs de la phase courante
3. Remplir le champ "Attente principale" et cliquer "Enregistrer"
   - Résultat attendu : le message "Enregistré ✓" s'affiche et le score de complétude se met à jour
4. Basculer en "Mode chat" — le chat reprend normalement, les données saisies sont conservées
5. Résultat attendu : pas de perte de données entre les deux modes

---

## S04 — Dashboard client — édition inline et badges provenance

**Prérequis :** session avec des données extraites, dashboard `/portal` accessible.

1. Accéder au dashboard `/portal`
2. Vérifier que les 6 onglets sont présents : Profil de vie, Projets, Risque et valeurs, Timing, Patrimoine, Revenus et flux
3. Dans l'onglet "Profil de vie", modifier le champ "Prénom" directement dans le formulaire inline et cliquer "Enregistrer"
   - Résultat attendu : le badge source passe de "Déclaré" à "Mis à jour" (jaune)
4. Vérifier la section "Historique récent" en bas de page : la modification apparaît avec la source, le rôle et l'horodatage
5. Résultat attendu : le score de complétude reste stable ou progresse si un champ manquant était renseigné

---

## S05 — Rapport beta

**Prérequis :** score de complétude ≥ 80 %.

1. Depuis le dashboard, cliquer "Voir mon rapport"
2. Vérifier que les 6 sections apparaissent avec les données du dossier
3. Chaque champ affiche un badge coloré indiquant sa source (déclaré, détecté, mis à jour, corrigé)
4. Cliquer "Imprimer / Exporter PDF" — la boîte de dialogue d'impression du navigateur s'ouvre
5. Résultat attendu : le rapport est lisible, les sources sont visibles, et le contenu correspond au dossier

**Test d'accès bloqué :** vider le dossier en dessous de 80 % → le bouton "Voir mon rapport" doit disparaître et `/portal/rapport` doit rediriger.

---

## S06 — Partage prescripteur (flux complet)

**Prérequis :** compte client avec un dossier partiellement rempli.

**Côté client :**
1. Aller sur `/portal/partage`
2. Remplir le formulaire : rôle "Notaire", blocs autorisés = "Profil de vie" + "Projets", note optionnelle
3. Cliquer "Créer l'invitation"
4. Un lien prescripteur apparaît — copier ce lien

**Côté prescripteur (navigation privée ou autre navigateur) :**
5. Coller le lien dans la barre d'adresse
6. Résultat attendu : la vue prescripteur s'ouvre sans demande de connexion
7. Vérifier que seuls les onglets "Profil de vie" et "Projets" sont visibles
8. Ouvrir le formulaire de correction d'un champ, saisir une nouvelle valeur et une raison, cliquer "Enregistrer la correction"
9. Résultat attendu : le message "Correction enregistrée" s'affiche en haut de page

**Retour côté client :**
10. Actualiser le dashboard `/portal`
11. Résultat attendu : une section "Corrections prescripteurs" apparaît avec le champ corrigé, le motif et la date
12. Le badge source du champ modifié est passé à "Corrigé" (vert)
13. Si `BREVO_ENABLED=1` : vérifier la réception de l'email de notification

---

## S07 — Rapport prescripteur

**Prérequis :** invitation prescripteur active avec au moins une correction enregistrée.

1. Depuis la vue prescripteur (lien `/prescripteur/{token}`), cliquer "Voir le rapport"
2. Résultat attendu :
   - Seuls les blocs autorisés apparaissent dans le rapport
   - Une section "Corrections professionnelles" liste les corrections faites via ce lien avec la raison et la date
3. Tenter d'accéder au rapport d'une invitation expirée/révoquée → page "Lien expiré" (HTTP 410)

---

## S08 — Révocation d'invitation

**Prérequis :** invitation prescripteur active (depuis S06).

1. Retourner sur `/portal/partage`
2. Cliquer "Révoquer" sur l'invitation créée
3. Résultat attendu : l'invitation disparaît de la liste
4. Essayer d'ouvrir le lien prescripteur précédemment copié → page "Lien expiré" (HTTP 410)

---

## S09 — Droits RGPD client

**Prérequis :** compte client connecté avec des données.

1. Aller sur `/portal/donnees`
2. Vérifier que les données personnelles principales s'affichent
3. Cliquer "Exporter mes données (JSON)" — un fichier JSON se télécharge
4. Ouvrir le fichier : vérifier que les champs du dossier sont présents
5. Cliquer "Demander la suppression de mon compte"
6. Résultat attendu : un message de confirmation indique que la demande est enregistrée et sera traitée sous 48h

---

## S10 — Conseiller : audit d'une session onboarding

**Prérequis :** compte conseiller interne (`ROLE_USER` ou `ROLE_ADMIN`), au moins une session client existante.

1. Aller sur `/onboarding`
2. La liste des sessions récentes s'affiche
3. Cliquer sur une session → page de détail
4. Vérifier la présence du tableau "Audit des modifications" avec la liste des `FieldEdit`
5. Vérifier le rapport de sourcing (déclaré / détecté / mis à jour / corrigé) en haut du tableau
6. Si la session est en cours : cliquer "Valider le profil" → page de revue avant conversion
7. Finaliser la session → un contact et un compte CRM sont créés, la session passe à "finalisée"

---

## S11 — Pilotage beta (administrateur)

**Prérequis :** compte avec rôle `ROLE_ADMIN`.

1. Aller sur `/pilotage`
2. Vérifier les indicateurs agrégés : nombre de sessions, taux de complétude moyen, abandons
3. Aller sur `/tenants` — créer un tenant de test, lui rattacher un utilisateur, vérifier la suspension
4. Aller sur `/users` — vérifier la liste des utilisateurs, modifier un rôle
5. Résultat attendu : aucune donnée de dossier individuel n'est accessible depuis ces pages

---

## Cas limites à vérifier dans tous les scénarios

| Cas | Comportement attendu |
| --- | --- |
| Lien prescripteur expiré (> 30 jours) | Page "Lien expiré" HTTP 410 |
| Score < 80 % → accès rapport | Redirection, bouton masqué |
| Champ `field_path` invalide en POST | Réponse 400, pas de crash |
| Client sans consentement → onboarding | Redirection vers l'écran de consentement |
| Session finalisée → reprise entretien | Redirection vers une nouvelle session |
| Prescripteur tente un bloc non autorisé | Flash d'erreur, correction rejetée |
