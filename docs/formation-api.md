# API Formation

Base des routes : `/api/v1`. Les routes `/admin` nécessitent un token Sanctum.

## Routes publiques

```text
GET  /formation-categories
GET  /formation-categories/{formationCategory}
GET  /formations
GET  /formations/{formation}
POST /formations/{formation}/participations
GET  /formation-images/{image}
```

Les GET des formations et catégories sont communs au site public et au back-office. Ils retournent les éléments actifs et inactifs non supprimés. Une formation dont la catégorie est soft-deleted reste exclue. Les Resources exposent `is_active` afin que chaque frontend applique l'affichage attendu.

La Resource Formation expose également `formation_category_id`, le statut, les dates, le nombre de places, les frais, la catégorie, `thumbnail_url` et la description. Les chemins de stockage, champs d'audit, participations et paiements ne sont pas exposés.

Filtres disponibles sur `GET /formations` :

- `formation_category_id` ;
- `status` ;
- `is_active`.

Payload d'inscription :

```json
{
  "nom": "Diallo",
  "prenom": "Aminata",
  "telephone": "+224 620-00-00-00",
  "adresse": "Conakry"
}
```

Les espaces, tirets et parenthèses du téléphone sont retirés. Un participant existant est réutilisé sans modification silencieuse de ses informations. Un participant ou une participation soft-deleted est restauré avec son historique.

L'inscription est autorisée pour une formation `en_attente` ou `en_cours`, même après `date_debut` ou `date_fin_inscription`. Elle est refusée si la formation est inactive, `annulee`, `terminee` ou complète.

## Routes administratives

```text
POST                  /admin/formation-categories
PUT|PATCH|DELETE      /admin/formation-categories/{formationCategory}

POST                   /admin/formations
PUT|PATCH|DELETE       /admin/formations/{formation}
PATCH                  /admin/formations/{formation}/switch-status
PATCH                  /admin/formations/{formation}/switch-state
POST                   /admin/formations/content-images

GET                    /admin/participants
GET|PUT|PATCH|DELETE   /admin/participants/{participant}

GET                    /admin/participations
GET|DELETE             /admin/participations/{participation}
PATCH                  /admin/participations/{participation}/switch-status

GET|POST               /admin/participations/{participation}/payments
GET|PUT|PATCH|DELETE   /admin/payments/{payment}
```

Les GET administratifs des formations et catégories n'existent plus. Le back-office utilise les GET publics communs. Les soft-deleted ne sont jamais exposés par ces routes.

Les listes administratives des participants et participations conservent `?trashed=with` et `?trashed=only`.

## Statuts

Formation :

- `en_attente`
- `en_cours`
- `terminee`
- `annulee`

Participation :

- `en_attente`
- `validee`
- `abandonnee`
- `terminee`

`switchStatus` reçoit explicitement le nouveau statut :

```json
{
  "status": "en_cours"
}
```

`switchState` ne reçoit aucun body et inverse uniquement `is_active`. Il ne modifie jamais le statut métier.

## Création d'une formation

Champs principaux :

```json
{
  "formation_category_id": 1,
  "libelle": "Laravel avancé",
  "short_description": "Créer des API robustes",
  "description": "<p>Contenu CKEditor</p>",
  "date_debut": "2026-08-10",
  "date_fin": "2026-08-12",
  "nombre_places": 20,
  "lieu_formation": "Conakry",
  "date_fin_inscription": "2026-08-01",
  "frais_inscription": 100000,
  "frais_formation": 500000
}
```

Le thumbnail optionnel est envoyé dans le champ multipart `thumbnail`. `date_fin` doit être postérieure ou égale à `date_debut`; aucune relation n'est imposée entre `date_fin_inscription` et `date_debut`.

## CKEditor

Le client envoie uniquement le fichier CKEditor dans le champ `upload` :

```text
POST /admin/formations/content-images
```

Réponse :

```json
{
  "url": "https://example.test/api/v1/formation-images/fichier.jpg"
}
```

Le backend génère un `draft_token` technique pour rester compatible avec le schéma existant. Le frontend ne l'envoie ni pendant l'upload, ni pendant la création ou modification de la formation.

Le HTML est nettoyé avec Purify. Une image est rattachée selon son chemin uniquement si elle a été téléversée par l'administrateur authentifié. Les images retirées du HTML sont supprimées. La commande quotidienne `formations:clean-orphan-images` supprime après 24 heures les images jamais rattachées.

## Paiements partiels

Cette première version gère uniquement les frais d'inscription. `frais_formation` est informatif et n'intervient dans aucun calcul automatique. Aucun type de paiement n'est stocké.

Payload :

```json
{
  "montant": 30000,
  "methode_paiement": "Espèces",
  "date_paiement": "2026-08-01",
  "commentaire": "Premier versement"
}
```

À l'inscription, `frais_inscription_requis` reçoit un instantané de `formation.frais_inscription`. Une modification ultérieure du tarif de la formation ne change donc pas le seuil historique.

Après chaque création, modification ou soft delete d'un paiement, `frais_inscription_paye` est recalculé à partir des paiements actifs. Quand le cumul atteint `frais_inscription_requis`, une participation `en_attente` passe à `validee`. Les statuts `abandonnee` et `terminee` ne changent jamais automatiquement.

Une participation déjà `validee` n'est pas rétrogradée automatiquement si une modification ou suppression de paiement fait redescendre le cumul sous le seuil.
