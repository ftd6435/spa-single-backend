# Documentation API — Champ `status` et endpoints `switch-status`

> Modules concernés : **Blog** (articles, commentaires), **Offer** (offres, types d'offre), **Contact** (messages)
> **Base URL** : `http://127.0.0.1:8000/api`
> Même format de réponse et mêmes headers que le reste de l'API
> (voir `documentation-gestion-blog-offer-contact.md`)

---

## Vue d'ensemble

Chaque élément possède désormais un champ **`status`** (booléen) qui contrôle sa visibilité
sur le site web :

- `true` → l'élément est **visible** (valeur par défaut à la création)
- `false` → l'élément est **masqué** : il disparaît des routes publiques, sans être supprimé

La visibilité se change **uniquement** via les endpoints `switch-status` (bascule on/off).
Un `status` envoyé dans un formulaire de création/modification est **ignoré** par l'API.

---

## Le champ `status` dans les réponses

`status` est présent dans toutes les réponses (articles, commentaires, offres, types
d'offre, contacts) :

```json
{
  "id": 3,
  "title": "Mon article",
  "status": true,
  "...": "..."
}
```

C'est ce champ qu'il faut brancher sur le toggle visible/masqué du back-office.

---

## Endpoints `switch-status`

**Accès** : Admin (token requis) — **Méthode** : `PATCH` — **Body** : aucun

| Endpoint | Effet quand désactivé |
|---|---|
| `PATCH /v1/admin/articles/{id}/switch-status` | l'article disparaît du blog |
| `PATCH /v1/admin/comments/{id}/switch-status` | le commentaire disparaît sous l'article (modération) |
| `PATCH /v1/admin/offers/{id}/switch-status` | l'offre disparaît de la page tarifs |
| `PATCH /v1/admin/offer-types/{id}/switch-status` | le type est désactivé (gestion back-office) |
| `PATCH /v1/admin/contacts/{id}/switch-status` | le message est désactivé (gestion back-office) |

Chaque appel **inverse** l'état actuel : visible → masqué, masqué → visible.

**Exemple** :
```
PATCH /api/v1/admin/articles/3/switch-status
Authorization: Bearer {token}
```

**Réponse 200** (l'élément vient d'être désactivé) :
```json
{
  "status": 1,
  "message": "Article désactivé avec succès.",
  "data": {
    "id": 3,
    "title": "Mon article",
    "status": false,
    "...": "..."
  }
}
```

Rappel : le `status: 1` à la racine est le format standard des réponses de l'API
(succès/échec) — ne pas le confondre avec le `data.status` de l'élément (visibilité).

**Réponse si l'id n'existe pas** :
```json
{ "status": 0, "message": "Article introuvable", "error": [] }
```

Chaque bascule est tracée dans les logs d'activité (qui, quoi, quand, nouvelle valeur).

---

## Lecture publique vs lecture admin ⚠️ IMPORTANT pour le front

Les routes **publiques** ne renvoient plus que les éléments **actifs**. De nouvelles routes
de lecture **admin** renvoient TOUT (y compris les masqués) :

| Contexte | Route à utiliser | Contenu |
|---|---|---|
| Site public — blog | `GET /v1/articles` et `GET /v1/articles/{id}` | articles actifs uniquement |
| Site public — commentaires | `GET /v1/articles/{article}/comments` | commentaires actifs d'un article actif |
| Site public — tarifs | `GET /v1/offers` et `GET /v1/offers/{id}` | offres actives uniquement |
| **Back-office** — blog | `GET /v1/admin/articles` et `GET /v1/admin/articles/{id}` | tous les articles |
| **Back-office** — commentaires | `GET /v1/admin/articles/{article}/comments` | tous les commentaires |
| **Back-office** — offres | `GET /v1/admin/offers` et `GET /v1/admin/offers/{id}` | toutes les offres |

> ⚠️ **Action requise côté back-office** : remplacer les appels de lecture des écrans
> d'administration par les routes `/v1/admin/...` ci-dessus. Sinon, un élément désactivé
> disparaît aussi de la liste d'admin et ne peut plus être réactivé depuis l'interface.
> Le format de réponse est identique, seul le chemin change (le token est déjà envoyé
> sur ces écrans). **Le site public, lui, n'a rien à changer.**

Les types d'offre et les contacts étaient déjà en lecture admin uniquement — rien à changer
pour eux.

---

## Règles de visibilité côté public

1. Le `show` public d'un élément désactivé répond **introuvable** (comme s'il n'existait pas).
2. Les commentaires désactivés n'apparaissent pas, et les commentaires d'un article
   désactivé non plus.
3. On ne peut pas **poster** de commentaire sur un article désactivé (réponse : introuvable).
4. Impossible de modifier `status` via les formulaires : seul `switch-status` le change.
