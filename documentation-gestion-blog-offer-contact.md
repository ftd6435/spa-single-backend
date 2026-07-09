# Documentation API — Modules Blog, Offer et Contact

> **Base URL** : `http://127.0.0.1:8000/api`
> **Format** : JSON
> **Header requis sur toutes les requêtes** :
> ```
> Accept: application/json
> Content-Type: application/json
> ```
> **Header requis sur les routes protégées** :
> ```
> Authorization: Bearer {token}
> ```

---

## Format de réponse standard

Toutes les réponses de l'API suivent ce format :

### Succès avec données
```json
{
  "status": 1,
  "message": "Message de succès",
  "data": { ... }
}
```

### Succès sans données (suppression, déconnexion)
```json
{
  "status": 1,
  "message": "Message de succès"
}
```

### Erreur
```json
{
  "status": 0,
  "message": "Message d'erreur",
  "error": {}
}
```

### Erreur de validation (422)
```json
{
  "message": "The title field is required.",
  "errors": {
    "title": ["The title field is required."]
  }
}
```

---

# MODULE BLOG

## Vue d'ensemble

Le module Blog gère les **articles**, les **images de leur contenu** (CKEditor) et leurs **commentaires**.

| Accès | Endpoints |
|---|---|
| Public (sans token) | Lister articles, Voir article, Afficher image du contenu, Lister commentaires, Poster commentaire |
| Admin (token requis) | Créer, modifier, supprimer article — Uploader image du contenu — Supprimer commentaire |

---

## 1. Articles

### GET /v1/articles — Lister tous les articles

**Accès** : Public

**Requête** :
```
GET /api/v1/articles
Accept: application/json
```

**Réponse 200** :
```json
{
  "status": 1,
  "message": "Liste des articles chargée avec succès.",
  "data": [
    {
      "id": 1,
      "title": "Introduction à Laravel",
      "short_description": "Un aperçu du framework Laravel",
      "description": "Laravel est un framework PHP moderne...",
      "cover_url": "https://r2.example.com/images/articles/uuid.jpg",
      "tags": [
        { "id": 1, "libelle": "Laravel" },
        { "id": 2, "libelle": "PHP" }
      ],
      "comments": null,
      "created_by": "Jean Dupont",
      "updated_by": null,
      "created_at": "22-06-2026 14:30:00",
      "updated_at": "22-06-2026 14:30:00"
    },
    {
      "id": 2,
      "title": "Les bases d'Angular",
      "short_description": null,
      "description": "Angular est un framework frontend...",
      "cover_url": null,
      "tags": [],
      "comments": null,
      "created_by": "Marie Martin",
      "updated_by": null,
      "created_at": "21-06-2026 10:00:00",
      "updated_at": "21-06-2026 10:00:00"
    }
  ]
}
```

---

### GET /v1/articles/{id} — Voir un article

**Accès** : Public

**Requête** :
```
GET /api/v1/articles/1
Accept: application/json
```

**Réponse 200** :
```json
{
  "status": 1,
  "message": "Article chargé avec succès.",
  "data": {
    "id": 1,
    "title": "Introduction à Laravel",
    "short_description": "Un aperçu du framework Laravel",
    "description": "Laravel est un framework PHP moderne et élégant...",
    "cover_url": "https://r2.example.com/images/articles/uuid.jpg",
    "tags": [
      { "id": 1, "libelle": "Laravel" },
      { "id": 2, "libelle": "PHP" }
    ],
    "comments": [
      {
        "id": 1,
        "name": "Alice",
        "email": "alice@example.com",
        "content": "Très bon article !",
        "created_at": "23-06-2026 09:15:00"
      }
    ],
    "created_by": "Jean Dupont",
    "updated_by": null,
    "created_at": "22-06-2026 14:30:00",
    "updated_at": "22-06-2026 14:30:00"
  }
}
```

**Réponse 404** :
```json
{
  "status": 0,
  "message": "Article introuvable",
  "error": {}
}
```

---

### POST /v1/admin/articles — Créer un article

**Accès** : Admin (token requis)
**Content-Type** : `multipart/form-data` (si envoi d'image), sinon `application/json`

**Payload** :
```
POST /api/v1/admin/articles
Authorization: Bearer {token}
Content-Type: multipart/form-data

title            (string, requis, 2-200 caractères)
description      (string HTML, requis, min 2 caractères — contenu produit par CKEditor, voir section "Images du contenu")
short_description (string, optionnel)
cover            (fichier image, optionnel, formats: png/jpg/jpeg/webp, max 2Mo)
tags             (tableau d'IDs, optionnel) → tags[]=1&tags[]=2
```

> ⚠️ **`description` contient du HTML** (formatage, emojis, images). Le backend le nettoie
> automatiquement (anti-XSS) : seules les balises de la liste blanche sont conservées
> (`h1-h6, p, br, strong, b, em, i, u, s, del, sub, sup, a, ul, ol, li, blockquote, pre, code,
> hr, img, figure, figcaption, table, thead, tbody, tr, th, td, span`).
> Tout `<script>`, attribut `onclick`, lien `javascript:` etc. est supprimé silencieusement.

**Exemple JSON (sans image)** :
```json
{
  "title": "Comprendre les Services Angular",
  "short_description": "Guide complet sur les services Angular",
  "description": "Les services Angular permettent de partager de la logique...",
  "tags": [1, 3]
}
```

**Réponse 200** :
```json
{
  "status": 1,
  "message": "Article créé avec succès.",
  "data": {
    "id": 3,
    "title": "Comprendre les Services Angular",
    "short_description": "Guide complet sur les services Angular",
    "description": "Les services Angular permettent de partager de la logique...",
    "cover_url": null,
    "tags": [
      { "id": 1, "libelle": "Angular" },
      { "id": 3, "libelle": "TypeScript" }
    ],
    "comments": null,
    "created_by": "Jean Dupont",
    "updated_by": null,
    "created_at": "29-06-2026 08:00:00",
    "updated_at": "29-06-2026 08:00:00"
  }
}
```

**Réponse 422 (validation échouée)** :
```json
{
  "message": "The title field is required.",
  "errors": {
    "title": ["The title field is required."],
    "description": ["The description field is required."]
  }
}
```

---

### PUT /v1/admin/articles/{id} — Modifier un article

**Accès** : Admin (token requis)
**Content-Type** : `multipart/form-data` ou `application/json`

> Pour envoyer un fichier via PUT en multipart, utiliser `POST` avec le champ `_method: PUT`
> (workaround HTML forms / Angular)

**Payload** :
```json
{
  "title": "Comprendre les Services Angular — Mise à jour",
  "short_description": "Guide mis à jour",
  "description": "Contenu mis à jour...",
  "tags": [1, 2, 3]
}
```

**Réponse 200** :
```json
{
  "status": 1,
  "message": "Article modifié avec succès.",
  "data": {
    "id": 3,
    "title": "Comprendre les Services Angular — Mise à jour",
    "short_description": "Guide mis à jour",
    "description": "Contenu mis à jour...",
    "cover_url": null,
    "tags": [
      { "id": 1, "libelle": "Angular" },
      { "id": 2, "libelle": "PHP" },
      { "id": 3, "libelle": "TypeScript" }
    ],
    "comments": null,
    "created_by": "Jean Dupont",
    "updated_by": "Marie Martin",
    "created_at": "29-06-2026 08:00:00",
    "updated_at": "29-06-2026 10:30:00"
  }
}
```

---

### DELETE /v1/admin/articles/{id} — Supprimer un article

**Accès** : Admin (token requis)

**Requête** :
```
DELETE /api/v1/admin/articles/3
Authorization: Bearer {token}
```

**Réponse 200** :
```json
{
  "status": 1,
  "message": "Article supprimé avec succès"
}
```

> La suppression d'un article supprime aussi sa cover **et toutes les images de son contenu**
> sur le stockage. Rien à faire côté front.

---

## 2. Images du contenu (CKEditor)

### Vue d'ensemble du fonctionnement

Le champ `description` d'un article est rédigé avec CKEditor, qui permet d'insérer des images
directement dans le texte. Le fonctionnement est le suivant :

```
1. Le rédacteur glisse une image dans CKEditor
2. CKEditor l'envoie IMMÉDIATEMENT à POST /v1/admin/articles/content-images
   (avant même que l'article ne soit enregistré)
3. L'API stocke l'image et répond : { "url": "http://.../api/v1/article-images/xxx.png" }
4. CKEditor insère <img src="cette-url"> dans le texte
5. À la soumission du formulaire, description part avec les <img> déjà dedans
6. Au GET /v1/articles/{id}, description revient telle quelle : il suffit de
   rendre le HTML, les images se chargent toutes seules
```

**Côté front, il n'y a donc qu'UNE chose à faire** : configurer l'upload adapter de CKEditor
pour pointer sur l'endpoint d'upload (voir configuration plus bas). Ne jamais laisser CKEditor
insérer des images en base64 dans le HTML.

---

### POST /v1/admin/articles/content-images — Uploader une image du contenu

**Accès** : Admin (token requis)
**Content-Type** : `multipart/form-data`

**Payload** :
```
POST /api/v1/admin/articles/content-images
Authorization: Bearer {token}
Content-Type: multipart/form-data

upload    (fichier image, requis, formats: png/jpg/jpeg/webp/gif, max 2Mo)
```

> Le champ s'appelle **`upload`** : c'est le nom que CKEditor (SimpleUploadAdapter)
> utilise par défaut, aucune configuration supplémentaire n'est nécessaire.

**Réponse 200** — ⚠️ format spécial CKEditor, PAS le format standard de l'API :
```json
{
  "url": "http://127.0.0.1:8000/api/v1/article-images/ef970f1c-bb4c-4580-8f53-5025f6b6e4a0.png"
}
```

CKEditor lit directement la clé `url` à la racine et insère l'image dans l'éditeur.

**Réponse 422 (validation échouée)** :
```json
{
  "message": "The upload field must be an image.",
  "errors": {
    "upload": ["The upload field must be an image."]
  }
}
```

---

### GET /v1/article-images/{nom} — Afficher une image du contenu

**Accès** : Public (aucun token)

**Requête** :
```
GET /api/v1/article-images/ef970f1c-bb4c-4580-8f53-5025f6b6e4a0.png
```

**Réponse** : `302 Redirect` vers le fichier sur le stockage Cloudflare R2.

> Cette route est faite pour être utilisée **dans les `src` des balises `<img>`** : le
> navigateur suit la redirection tout seul. Il n'y a rien à coder côté front, et il ne faut
> PAS l'appeler en HttpClient/fetch. C'est cette indirection qui garantit que les images
> des articles ne cassent jamais (les URLs directes du stockage expirent après 7 jours).

---

### Configuration CKEditor côté Angular

Exemple avec le **SimpleUploadAdapter** de CKEditor 5 :

```typescript
public editorConfig = {
  simpleUpload: {
    uploadUrl: environment.apiUrl + '/v1/admin/articles/content-images',
    withCredentials: false,
    headers: {
      Authorization: 'Bearer ' + this.authService.getToken(),
    },
  },
};
```

```html
<ckeditor [editor]="Editor" [config]="editorConfig" formControlName="description"></ckeditor>
```

**Points d'attention pour le front** :

1. **Emojis et formatage** : gérés nativement, rien à faire. Le HTML est nettoyé côté
   serveur (voir liste blanche dans la section "Créer un article").
2. **Affichage de la description** : c'est du HTML → utiliser `[innerHTML]="article.description"`
   dans Angular (le HTML est déjà sanitizé côté serveur, mais Angular re-sanitize par défaut,
   utiliser `DomSanitizer.bypassSecurityTrustHtml` si le rendu supprime des éléments).
3. **Image supprimée du texte pendant l'édition** : rien à faire, le backend compare
   l'ancienne et la nouvelle description à chaque `PUT` et supprime du stockage les images
   qui ne sont plus référencées.
4. **Rédaction abandonnée** (images uploadées mais article jamais enregistré) : rien à
   faire, un nettoyage automatique tourne chaque jour côté serveur et purge les images
   jamais rattachées depuis plus de 24h.

---

## 3. Commentaires

### GET /v1/articles/{article}/comments — Lister les commentaires

**Accès** : Public

**Requête** :
```
GET /api/v1/articles/1/comments
Accept: application/json
```

**Réponse 200** :
```json
{
  "status": 1,
  "message": "Liste des commentaires chargée avec succès.",
  "data": [
    {
      "id": 2,
      "name": "Bob",
      "email": "bob@example.com",
      "content": "Merci pour cet article !",
      "created_at": "29-06-2026 11:00:00"
    },
    {
      "id": 1,
      "name": "Alice",
      "email": "alice@example.com",
      "content": "Très intéressant.",
      "created_at": "28-06-2026 09:15:00"
    }
  ]
}
```

---

### POST /v1/articles/{article}/comments — Poster un commentaire

**Accès** : Public (visiteur non connecté accepté)

**Payload** :
```json
{
  "name": "Alice",
  "email": "alice@example.com",
  "content": "Très bon article, merci !"
}
```

| Champ | Type | Requis | Contraintes |
|---|---|---|---|
| name | string | Oui | min: 2, max: 160 |
| email | string | Oui | format email valide, max: 255 |
| content | string | Oui | min: 2 |

**Réponse 200** :
```json
{
  "status": 1,
  "message": "Commentaire enregistré avec succès.",
  "data": {
    "id": 3,
    "name": "Alice",
    "email": "alice@example.com",
    "content": "Très bon article, merci !",
    "created_at": "29-06-2026 12:00:00"
  }
}
```

---

### DELETE /v1/admin/comments/{id} — Supprimer un commentaire

**Accès** : Admin (token requis)

**Requête** :
```
DELETE /api/v1/admin/comments/3
Authorization: Bearer {token}
```

**Réponse 200** :
```json
{
  "status": 1,
  "message": "Commentaire supprimé avec succès"
}
```

---

# MODULE OFFER

## Vue d'ensemble

Le module Offer gère les **types d'offres** (catégories) et les **offres** (plans tarifaires).

| Accès | Endpoints |
|---|---|
| Public (sans token) | Lister offres, Voir une offre |
| Admin (token requis) | CRUD complet offres — CRUD complet types d'offres |

---

## 1. Types d'Offres

### GET /v1/admin/offer-types — Lister les types d'offres

**Accès** : Admin (token requis)

**Réponse 200** :
```json
{
  "status": 1,
  "message": "Liste des types d'offre chargée avec succès.",
  "data": [
    {
      "id": 1,
      "name": "Mensuel",
      "description": "Facturation mensuelle",
      "created_at": "22-06-2026 10:00:00",
      "updated_at": "22-06-2026 10:00:00"
    },
    {
      "id": 2,
      "name": "Annuel",
      "description": "Facturation annuelle avec réduction",
      "created_at": "22-06-2026 10:05:00",
      "updated_at": "22-06-2026 10:05:00"
    }
  ]
}
```

---

### POST /v1/admin/offer-types — Créer un type d'offre

**Accès** : Admin (token requis)

**Payload** :
```json
{
  "name": "Trimestriel",
  "description": "Facturation tous les 3 mois"
}
```

| Champ | Type | Requis | Contraintes |
|---|---|---|---|
| name | string | Oui | min: 2, max: 160, unique |
| description | string | Non | min: 2 |

**Réponse 200** :
```json
{
  "status": 1,
  "message": "Type d'offre créé avec succès.",
  "data": {
    "id": 3,
    "name": "Trimestriel",
    "description": "Facturation tous les 3 mois",
    "created_at": "29-06-2026 08:00:00",
    "updated_at": "29-06-2026 08:00:00"
  }
}
```

---

### PUT /v1/admin/offer-types/{id} — Modifier un type d'offre

**Accès** : Admin (token requis)

**Payload** :
```json
{
  "name": "Trimestriel",
  "description": "Facturation tous les 3 mois — mise à jour"
}
```

**Réponse 200** :
```json
{
  "status": 1,
  "message": "Type d'offre modifié avec succès.",
  "data": {
    "id": 3,
    "name": "Trimestriel",
    "description": "Facturation tous les 3 mois — mise à jour",
    "created_at": "29-06-2026 08:00:00",
    "updated_at": "29-06-2026 09:00:00"
  }
}
```

---

### DELETE /v1/admin/offer-types/{id} — Supprimer un type d'offre

**Accès** : Admin (token requis)

**Réponse 200** :
```json
{
  "status": 1,
  "message": "Type d'offre supprimé avec succès"
}
```

---

## 2. Offres

### GET /v1/offers — Lister toutes les offres

**Accès** : Public

**Réponse 200** :
```json
{
  "status": 1,
  "message": "Liste des offres chargée avec succès.",
  "data": [
    {
      "id": 1,
      "plan": "Starter",
      "price": "29.99",
      "features": [
        "5 projets",
        "10 Go de stockage",
        "Support email"
      ],
      "is_popular": false,
      "offer_type": {
        "id": 1,
        "name": "Mensuel",
        "description": "Facturation mensuelle",
        "created_at": "22-06-2026 10:00:00",
        "updated_at": "22-06-2026 10:00:00"
      },
      "created_at": "22-06-2026 14:00:00",
      "updated_at": "22-06-2026 14:00:00"
    },
    {
      "id": 2,
      "plan": "Pro",
      "price": "79.99",
      "features": [
        "Projets illimités",
        "100 Go de stockage",
        "Support prioritaire",
        "API accès"
      ],
      "is_popular": true,
      "offer_type": {
        "id": 1,
        "name": "Mensuel",
        "description": "Facturation mensuelle",
        "created_at": "22-06-2026 10:00:00",
        "updated_at": "22-06-2026 10:00:00"
      },
      "created_at": "22-06-2026 14:10:00",
      "updated_at": "22-06-2026 14:10:00"
    }
  ]
}
```

---

### GET /v1/offers/{id} — Voir une offre

**Accès** : Public

**Réponse 200** :
```json
{
  "status": 1,
  "message": "Offre chargée avec succès.",
  "data": {
    "id": 2,
    "plan": "Pro",
    "price": "79.99",
    "features": [
      "Projets illimités",
      "100 Go de stockage",
      "Support prioritaire",
      "API accès"
    ],
    "is_popular": true,
    "offer_type": {
      "id": 1,
      "name": "Mensuel",
      "description": "Facturation mensuelle",
      "created_at": "22-06-2026 10:00:00",
      "updated_at": "22-06-2026 10:00:00"
    },
    "created_at": "22-06-2026 14:10:00",
    "updated_at": "22-06-2026 14:10:00"
  }
}
```

---

### POST /v1/admin/offers — Créer une offre

**Accès** : Admin (token requis)

**Payload** :
```json
{
  "offer_type_id": 1,
  "plan": "Enterprise",
  "price": 199.99,
  "features": [
    "Projets illimités",
    "1 To de stockage",
    "Support dédié 24/7",
    "SLA garanti",
    "Déploiement on-premise"
  ],
  "is_popular": false
}
```

| Champ | Type | Requis | Contraintes |
|---|---|---|---|
| offer_type_id | integer | Oui | doit exister dans offer_types |
| plan | string | Oui | min: 2, max: 160 |
| price | numeric | Non | min: 0 |
| features | array | Non | tableau de strings |
| features.* | string | — | min: 1 caractère |
| is_popular | boolean | Non | true ou false |

**Réponse 200** :
```json
{
  "status": 1,
  "message": "Offre créée avec succès.",
  "data": {
    "id": 3,
    "plan": "Enterprise",
    "price": "199.99",
    "features": [
      "Projets illimités",
      "1 To de stockage",
      "Support dédié 24/7",
      "SLA garanti",
      "Déploiement on-premise"
    ],
    "is_popular": false,
    "offer_type": {
      "id": 1,
      "name": "Mensuel",
      "description": "Facturation mensuelle",
      "created_at": "22-06-2026 10:00:00",
      "updated_at": "22-06-2026 10:00:00"
    },
    "created_at": "29-06-2026 08:00:00",
    "updated_at": "29-06-2026 08:00:00"
  }
}
```

---

### PUT /v1/admin/offers/{id} — Modifier une offre

**Accès** : Admin (token requis)

**Payload** :
```json
{
  "offer_type_id": 2,
  "plan": "Enterprise",
  "price": 1999.99,
  "features": [
    "Projets illimités",
    "1 To de stockage",
    "Support dédié 24/7"
  ],
  "is_popular": true
}
```

**Réponse 200** :
```json
{
  "status": 1,
  "message": "Offre modifiée avec succès.",
  "data": {
    "id": 3,
    "plan": "Enterprise",
    "price": "1999.99",
    "features": [
      "Projets illimités",
      "1 To de stockage",
      "Support dédié 24/7"
    ],
    "is_popular": true,
    "offer_type": {
      "id": 2,
      "name": "Annuel",
      "description": "Facturation annuelle avec réduction",
      "created_at": "22-06-2026 10:05:00",
      "updated_at": "22-06-2026 10:05:00"
    },
    "created_at": "29-06-2026 08:00:00",
    "updated_at": "29-06-2026 10:00:00"
  }
}
```

---

### DELETE /v1/admin/offers/{id} — Supprimer une offre

**Accès** : Admin (token requis)

**Réponse 200** :
```json
{
  "status": 1,
  "message": "Offre supprimée avec succès"
}
```

---

# MODULE CONTACT

## Vue d'ensemble

Le module Contact gère les **messages envoyés via le formulaire de contact** du site.

| Accès | Endpoints |
|---|---|
| Public (sans token) | Envoyer un message |
| Admin (token requis) | Lister, voir, supprimer les messages |

---

### POST /v1/contacts — Envoyer un message de contact

**Accès** : Public (aucun token requis)

**Payload** :
```json
{
  "name": "Alice Dupont",
  "email": "alice.dupont@example.com",
  "phone": "621000000",
  "company": "Tech Corp",
  "subject": "Demande de devis",
  "message": "Bonjour, je souhaite obtenir un devis pour votre offre Enterprise."
}
```

| Champ | Type | Requis | Contraintes |
|---|---|---|---|
| name | string | Oui | min: 2, max: 160 |
| email | string | Oui | format email valide, max: 255 |
| phone | string | Non | max: 20 |
| company | string | Non | max: 160 |
| subject | string | Oui | min: 2, max: 200 |
| message | string | Oui | min: 2 |

**Réponse 200** :
```json
{
  "status": 1,
  "message": "Votre message a bien été envoyé.",
  "data": {
    "id": 1,
    "name": "Alice Dupont",
    "email": "alice.dupont@example.com",
    "phone": "621000000",
    "company": "Tech Corp",
    "subject": "Demande de devis",
    "message": "Bonjour, je souhaite obtenir un devis pour votre offre Enterprise.",
    "created_at": "29-06-2026 14:00:00"
  }
}
```

**Réponse 422 (validation échouée)** :
```json
{
  "message": "The email field must be a valid email address.",
  "errors": {
    "email": ["The email field must be a valid email address."],
    "subject": ["The subject field is required."]
  }
}
```

---

### GET /v1/admin/contacts — Lister les messages

**Accès** : Admin (token requis)

**Réponse 200** :
```json
{
  "status": 1,
  "message": "Liste des messages de contact chargée avec succès.",
  "data": [
    {
      "id": 2,
      "name": "Bob Martin",
      "email": "bob@example.com",
      "phone": null,
      "company": null,
      "subject": "Question sur l'offre Pro",
      "message": "Est-ce que l'offre Pro inclut un accès API ?",
      "created_at": "29-06-2026 15:00:00"
    },
    {
      "id": 1,
      "name": "Alice Dupont",
      "email": "alice.dupont@example.com",
      "phone": "621000000",
      "company": "Tech Corp",
      "subject": "Demande de devis",
      "message": "Bonjour, je souhaite obtenir un devis...",
      "created_at": "29-06-2026 14:00:00"
    }
  ]
}
```

---

### GET /v1/admin/contacts/{id} — Voir un message

**Accès** : Admin (token requis)

**Réponse 200** :
```json
{
  "status": 1,
  "message": "Message de contact chargé avec succès.",
  "data": {
    "id": 1,
    "name": "Alice Dupont",
    "email": "alice.dupont@example.com",
    "phone": "621000000",
    "company": "Tech Corp",
    "subject": "Demande de devis",
    "message": "Bonjour, je souhaite obtenir un devis pour votre offre Enterprise.",
    "created_at": "29-06-2026 14:00:00"
  }
}
```

**Réponse 404** :
```json
{
  "status": 0,
  "message": "Message de contact introuvable",
  "error": {}
}
```

---

### DELETE /v1/admin/contacts/{id} — Supprimer un message

**Accès** : Admin (token requis)

**Réponse 200** :
```json
{
  "status": 1,
  "message": "Message de contact supprimé avec succès"
}
```

---

# Récapitulatif des endpoints

## Module Blog

| Méthode | URL | Auth | Description |
|---|---|---|---|
| GET | `/api/v1/articles` | Non | Lister les articles |
| GET | `/api/v1/articles/{id}` | Non | Voir un article |
| POST | `/api/v1/admin/articles` | Oui | Créer un article |
| PUT | `/api/v1/admin/articles/{id}` | Oui | Modifier un article |
| DELETE | `/api/v1/admin/articles/{id}` | Oui | Supprimer un article |
| GET | `/api/v1/articles/{article}/comments` | Non | Lister les commentaires |
| POST | `/api/v1/articles/{article}/comments` | Non | Poster un commentaire |
| DELETE | `/api/v1/admin/comments/{id}` | Oui | Supprimer un commentaire |

## Module Offer

| Méthode | URL | Auth | Description |
|---|---|---|---|
| GET | `/api/v1/offers` | Non | Lister les offres |
| GET | `/api/v1/offers/{id}` | Non | Voir une offre |
| POST | `/api/v1/admin/offers` | Oui | Créer une offre |
| PUT | `/api/v1/admin/offers/{id}` | Oui | Modifier une offre |
| DELETE | `/api/v1/admin/offers/{id}` | Oui | Supprimer une offre |
| GET | `/api/v1/admin/offer-types` | Oui | Lister les types d'offres |
| GET | `/api/v1/admin/offer-types/{id}` | Oui | Voir un type d'offre |
| POST | `/api/v1/admin/offer-types` | Oui | Créer un type d'offre |
| PUT | `/api/v1/admin/offer-types/{id}` | Oui | Modifier un type d'offre |
| DELETE | `/api/v1/admin/offer-types/{id}` | Oui | Supprimer un type d'offre |

## Module Contact

| Méthode | URL | Auth | Description |
|---|---|---|---|
| POST | `/api/v1/contacts` | Non | Envoyer un message |
| GET | `/api/v1/admin/contacts` | Oui | Lister les messages |
| GET | `/api/v1/admin/contacts/{id}` | Oui | Voir un message |
| DELETE | `/api/v1/admin/contacts/{id}` | Oui | Supprimer un message |

---

# Notes pour Angular

## Intercepteur HTTP — ajout automatique du token

```typescript
// auth.interceptor.ts
intercept(req: HttpRequest<any>, next: HttpHandler) {
  const token = localStorage.getItem('token');
  if (token) {
    req = req.clone({
      setHeaders: { Authorization: `Bearer ${token}` }
    });
  }
  return next.handle(req);
}
```

## Upload d'image avec FormData

```typescript
// Pour créer/modifier un article avec image
const formData = new FormData();
formData.append('title', 'Mon titre');
formData.append('description', 'Mon contenu');
formData.append('cover', imageFile);
formData.append('tags[]', '1');
formData.append('tags[]', '2');

// Pour PUT avec fichier, passer _method: PUT
formData.append('_method', 'PUT');
this.http.post(`/api/v1/admin/articles/${id}`, formData);
```

## Vérifier le statut de la réponse

```typescript
// Toutes les réponses ont status: 1 (succès) ou status: 0 (erreur)
this.http.get('/api/v1/articles').subscribe((res: any) => {
  if (res.status === 1) {
    this.articles = res.data;
  }
});
```
