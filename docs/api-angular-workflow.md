# Documentation API — Workflow Angular

## 1. Introduction

Cette documentation est destinée au développeur frontend Angular. Elle décrit comment consommer l'API Laravel du projet `spa-single-backend`, quelles routes sont publiques, quelles routes nécessitent un token Sanctum, quels payloads envoyer et dans quel ordre appeler les ressources.

Les informations ci-dessous sont basées sur les routes, contrôleurs, FormRequests, modèles et migrations actuellement présents dans le projet.

## 2. Base URL

Base locale recommandée :

```txt
http://localhost:8000/api
```

Les routes métier commencent généralement par `/v1`.

Exemple :

```txt
http://localhost:8000/api/v1/projects
```

## 3. Format standard des réponses API

Les contrôleurs utilisent principalement le trait `ApiResponses`.

Réponse de succès avec données :

```json
{
  "status": 1,
  "message": "Message de succès",
  "data": {}
}
```

Réponse de succès sans objet métier, souvent après suppression ou logout :

```json
{
  "status": 1,
  "message": "Ressource supprimée avec succès."
}
```

Réponse d'erreur métier, par exemple ressource introuvable :

```json
{
  "status": 0,
  "message": "Ressource introuvable.",
  "error": []
}
```

Réponse d'authentification avec token :

```json
{
  "status": 1,
  "data": {
    "id": 1,
    "name": "Administrateur"
  },
  "token": "plain-text-sanctum-token",
  "message": "Utilisateur connecté avec succès."
}
```

Les erreurs de validation Laravel retournent généralement un statut HTTP `422` avec une structure de ce type :

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "title": [
      "The title field is required."
    ]
  }
}
```

Les routes protégées sans token retournent généralement `401 Unauthorized`.

## 4. Authentification Sanctum

Workflow recommandé côté Angular :

1. Inscrire éventuellement un administrateur.
2. Connecter l'utilisateur avec son téléphone et son mot de passe.
3. Récupérer le champ `token` dans la réponse.
4. Stocker le token côté Angular, par exemple dans un service d'authentification.
5. Ajouter `Authorization: Bearer <token>` aux requêtes protégées.
6. Appeler logout pour supprimer les tokens côté backend.

### Register

```http
POST /v1/auth/register
```

Payload JSON :

```json
{
  "name": "Administrateur",
  "telephone": "620000000",
  "email": "admin@example.com",
  "password": "password",
  "password_confirmation": "password"
}
```

Payload `FormData` avec avatar optionnel :

```ts
const formData = new FormData();
formData.append('name', 'Administrateur');
formData.append('telephone', '620000000');
formData.append('email', 'admin@example.com');
formData.append('password', 'password');
formData.append('password_confirmation', 'password');
formData.append('avatar', avatarFile);
```

Contraintes principales :

- `telephone` unique, min 9, max 14 caractères.
- `email` unique et valide.
- `password` min 6 caractères et confirmé.
- `avatar` optionnel, image `png`, `jpg` ou `jpeg`, max 2048 Ko.

Réponse de succès :

```json
{
  "status": 1,
  "data": {
    "id": 1,
    "name": "Administrateur",
    "telephone": "620000000",
    "email": "admin@example.com"
  },
  "token": "plain-text-sanctum-token",
  "message": "Utilisateur créé avec succès."
}
```

### Login

```http
POST /v1/auth/login
```

Payload :

```json
{
  "telephone": "620000000",
  "password": "password"
}
```

Réponse de succès :

```json
{
  "status": 1,
  "data": {
    "id": 1,
    "name": "Administrateur",
    "telephone": "620000000"
  },
  "token": "plain-text-sanctum-token",
  "message": "Utilisateur connecté avec succès."
}
```

Erreur d'identifiants invalides :

```json
{
  "status": 0,
  "message": "Information invalide",
  "error": []
}
```

### Logout

```http
POST /v1/auth/logout
Authorization: Bearer <token>
```

Réponse :

```json
{
  "status": 1,
  "message": "Utilisateur deconnecté avec succès."
}
```

## 5. Headers recommandés côté Angular

Pour les requêtes JSON :

```http
Accept: application/json
Content-Type: application/json
Authorization: Bearer <token>
```

Pour les routes publiques, le header `Authorization` n'est pas nécessaire.

Pour les uploads :

- utiliser `FormData`;
- ne pas forcer manuellement `Content-Type: multipart/form-data`;
- laisser le navigateur définir automatiquement le `boundary`;
- garder `Accept: application/json`;
- ajouter `Authorization: Bearer <token>` si la route est protégée.

## 6. Routes publiques

Routes publiques réellement présentes :

```http
GET /v1/projects
GET /v1/projects/{id}
GET /v1/testimonials
GET /v1/visions
```

### Projets publics

```http
GET /v1/projects
GET /v1/projects/{id}
```

Filtres disponibles sur la liste :

- `category_id`
- `service_id`

Exemples :

```txt
GET /v1/projects?category_id=1
GET /v1/projects?service_id=2
GET /v1/projects?category_id=1&service_id=2
```

Les filtres doivent correspondre à des IDs existants. Sinon Laravel retourne une erreur `422`.

Réponse indicative :

```json
{
  "status": 1,
  "message": "Liste des projets chargée avec succès.",
  "data": [
    {
      "id": 1,
      "category_id": 1,
      "service_id": 2,
      "title": "Plateforme de gestion",
      "short_description": "Application web de gestion interne.",
      "description": "Projet complet de gestion administrative.",
      "demo_link": "https://demo.example.com",
      "category": {
        "id": 1,
        "libelle": "Applications web",
        "description": "Projets web",
        "status": true
      },
      "service": {
        "id": 2,
        "icon": "code",
        "image_path": "image.jpg",
        "image_url": "https://...",
        "title": "Développement web",
        "short_description": "Applications web",
        "description": "Service complet",
        "benefits": ["Code maintenable"]
      }
    }
  ]
}
```

### Témoignages publics

```http
GET /v1/testimonials
```

La réponse publique contient le témoignage, le client et le projet, sans champs d'audit.

```json
{
  "status": 1,
  "message": "Liste des témoignages chargée avec succès.",
  "data": [
    {
      "id": 1,
      "content": "Une excellente collaboration.",
      "client": {
        "id": 1,
        "first_name": "Mamadou",
        "last_name": "Diallo",
        "job_title": "Directeur"
      },
      "project": {
        "id": 1,
        "title": "Portail institutionnel",
        "short_description": "Présentation des activités."
      }
    }
  ]
}
```

### Visions publiques

```http
GET /v1/visions
```

```json
{
  "status": 1,
  "message": "Liste des visions chargée avec succès.",
  "data": [
    {
      "id": 1,
      "title": "Notre vision",
      "description": "Construire des solutions numériques utiles et durables.",
      "author": "SPA Technology"
    }
  ]
}
```

Note : aucune route publique pour les statistiques n'est actuellement déclarée. Les statistiques sont exposées uniquement via les routes admin protégées.

## 7. Routes administrateur protégées

Toutes les routes sous `/v1/admin` nécessitent :

```http
Authorization: Bearer <token>
Accept: application/json
```

Les ressources CRUD utilisent les méthodes suivantes :

```http
GET /v1/admin/{resource}
POST /v1/admin/{resource}
GET /v1/admin/{resource}/{id}
PUT /v1/admin/{resource}/{id}
PATCH /v1/admin/{resource}/{id}
DELETE /v1/admin/{resource}/{id}
```

Les FormRequests du module Website acceptent des mises à jour partielles en `PUT/PATCH`. Les champs principaux sont obligatoires en création `POST`, puis optionnels en modification.

### Clients

```http
GET /v1/admin/clients
POST /v1/admin/clients
GET /v1/admin/clients/{id}
PUT/PATCH /v1/admin/clients/{id}
DELETE /v1/admin/clients/{id}
```

Payload :

```json
{
  "first_name": "Mohamed",
  "last_name": "Camara",
  "job_title": "CEO"
}
```

Contraintes :

- `first_name` requis en création, string, min 2, max 160.
- `last_name` requis en création, string, min 2, max 160.
- `job_title` nullable, string, max 160.

### Partenaires

```http
GET /v1/admin/partners
POST /v1/admin/partners
GET /v1/admin/partners/{id}
PUT/PATCH /v1/admin/partners/{id}
DELETE /v1/admin/partners/{id}
```

Payload JSON sans logo :

```json
{
  "name": "SPA Technology",
  "acronym": "SPA",
  "domain": "Technologie",
  "description": "Partenaire technologique."
}
```

Payload `FormData` avec logo :

```ts
const formData = new FormData();
formData.append('name', 'SPA Technology');
formData.append('acronym', 'SPA');
formData.append('domain', 'Technologie');
formData.append('description', 'Partenaire technologique.');
formData.append('logo', logoFile);
```

Contraintes :

- `name` requis en création, string, min 2, max 160.
- `acronym` nullable, string, max 50.
- `domain` nullable, string, max 160.
- `description` nullable.
- `logo` optionnel, image `png`, `jpg` ou `jpeg`, max 2048 Ko.

La réponse peut contenir `logo_path` et l'attribut calculé `logo_url`.

### Services

```http
GET /v1/admin/services
POST /v1/admin/services
GET /v1/admin/services/{id}
PUT/PATCH /v1/admin/services/{id}
DELETE /v1/admin/services/{id}
```

Payload JSON sans image :

```json
{
  "icon": "code",
  "title": "Développement web",
  "short_description": "Création d'applications web modernes.",
  "description": "Service complet de développement web.",
  "benefits": [
    "Application rapide",
    "Code maintenable",
    "Architecture robuste"
  ],
  "tag_ids": [1, 2, 3]
}
```

Payload `FormData` avec image :

```ts
const formData = new FormData();
formData.append('icon', 'code');
formData.append('title', 'Développement web');
formData.append('short_description', 'Création d applications web modernes.');
formData.append('description', 'Service complet de développement web.');
formData.append('benefits[0]', 'Application rapide');
formData.append('benefits[1]', 'Code maintenable');
formData.append('tag_ids[0]', '1');
formData.append('tag_ids[1]', '2');
formData.append('image', imageFile);
```

Contraintes :

- `icon` nullable, string, max 255.
- `image` optionnelle, image `png`, `jpg`, `jpeg` ou `webp`, max 2048 Ko.
- `title` requis en création, string, min 2, max 160.
- `short_description` requis en création, string, min 2, max 1000.
- `description` requis en création, string, min 2.
- `benefits` nullable, tableau.
- `benefits.*` string, min 2.
- `tag_ids` nullable, tableau.
- `tag_ids.*` entier, distinct, doit exister dans `tags`.

Gestion des tags :

- les tags sont gérés via `tag_ids` dans le payload du service;
- il n'existe pas de route actuelle dédiée à la gestion directe de la table pivot `service_tag`;
- en création, si `tag_ids` est absent ou vide, aucun tag n'est associé;
- en modification, si `tag_ids` est absent, les tags existants ne changent pas;
- en modification, si `tag_ids` est présent avec `[]`, tous les tags sont retirés;
- en modification, si `tag_ids` contient des IDs, les tags sont synchronisés avec cette liste.

La réponse peut contenir `image_path`, `image_url` et la relation `tags`.

### Statistiques

```http
GET /v1/admin/statistics
POST /v1/admin/statistics
GET /v1/admin/statistics/{id}
PUT/PATCH /v1/admin/statistics/{id}
DELETE /v1/admin/statistics/{id}
```

Payload :

```json
{
  "label": "Projets réalisés",
  "value": 25,
  "unit": "+"
}
```

Contraintes :

- `label` requis en création, string, min 2, max 160.
- `value` requis en création, numérique, min 0.
- `unit` nullable, string, max 20.

### Visions

```http
GET /v1/admin/visions
POST /v1/admin/visions
GET /v1/admin/visions/{id}
PUT/PATCH /v1/admin/visions/{id}
DELETE /v1/admin/visions/{id}
```

Payload :

```json
{
  "title": "Notre vision",
  "description": "Construire des solutions numériques utiles et durables.",
  "author": "SPA Technology"
}
```

Contraintes :

- `title` requis en création, string, min 2, max 160.
- `description` requis en création, string, min 2.
- `author` nullable, string, max 160.

### Projets

```http
GET /v1/admin/projects
POST /v1/admin/projects
GET /v1/admin/projects/{id}
PUT/PATCH /v1/admin/projects/{id}
DELETE /v1/admin/projects/{id}
```

Payload :

```json
{
  "category_id": 1,
  "service_id": 2,
  "title": "Plateforme de gestion",
  "short_description": "Application web de gestion interne.",
  "description": "Projet complet de gestion administrative.",
  "demo_link": "https://demo.example.com"
}
```

Contraintes :

- `category_id` requis en création, entier, doit exister dans `categories`.
- `service_id` nullable, entier, doit exister dans `services` si fourni.
- `title` requis en création, string, min 2, max 160.
- `short_description` requis en création, string, min 2, max 1000.
- `description` requis en création, string, min 2.
- `demo_link` nullable, URL valide, max 255.

Relations importantes :

- une catégorie doit exister avant de créer un projet;
- un service peut être associé au projet, mais il peut aussi être `null`;
- la suppression d'une catégorie utilisée par un projet est restreinte en base;
- la suppression d'un service met `service_id` à `null` sur les projets liés.

### Témoignages

```http
GET /v1/admin/testimonials
POST /v1/admin/testimonials
GET /v1/admin/testimonials/{id}
PUT/PATCH /v1/admin/testimonials/{id}
DELETE /v1/admin/testimonials/{id}
```

Payload :

```json
{
  "project_id": 1,
  "client_id": 1,
  "content": "L'équipe a livré une solution fiable et professionnelle."
}
```

Contraintes :

- `project_id` requis en création, entier, doit exister dans `projects`.
- `client_id` requis en création, entier, doit exister dans `clients`.
- `content` requis en création, string, min 2.

Relations importantes :

- `client_id` doit correspondre à un client existant;
- `project_id` doit correspondre à un projet existant;
- la suppression d'un client supprime ses témoignages;
- la suppression d'un projet supprime ses témoignages.

## 8. Ressources Settings utiles au frontend admin

Les routes Settings sont protégées par Sanctum et disponibles sous `/v1/admin`.

### Catégories

```http
GET /v1/admin/categories
POST /v1/admin/categories
GET /v1/admin/categories/{id}
PUT/PATCH /v1/admin/categories/{id}
DELETE /v1/admin/categories/{id}
GET /v1/admin/categories/{id}/category
```

Payload :

```json
{
  "libelle": "Applications web",
  "description": "Projets web et plateformes digitales."
}
```

Contraintes :

- `libelle` requis, unique, string, min 2, max 160.
- `description` nullable, string, min 2.

La route `GET /v1/admin/categories/{id}/category` inverse le statut booléen de la catégorie.

Note : contrairement aux FormRequests du module Website, `CategoryRequest` demande `libelle` même en mise à jour.

### Tags

```http
GET /v1/admin/tags
POST /v1/admin/tags
GET /v1/admin/tags/{id}
PUT/PATCH /v1/admin/tags/{id}
DELETE /v1/admin/tags/{id}
GET /v1/admin/tags/{id}/tag
```

Payload :

```json
{
  "libelle": "Laravel",
  "description": "Backend PHP Laravel."
}
```

Contraintes :

- `libelle` requis, unique, string, min 2, max 160.
- `description` nullable, string, min 2.

La route `GET /v1/admin/tags/{id}/tag` inverse le statut booléen du tag.

Les catégories sont nécessaires avant de créer certains projets. Les tags sont nécessaires avant d'associer des tags aux services via `tag_ids`.

## 9. Workflow recommandé côté Angular

### Côté public

1. Charger les visions avec `GET /v1/visions`.
2. Charger les projets avec `GET /v1/projects`.
3. Appliquer éventuellement les filtres `category_id` ou `service_id`.
4. Charger les témoignages avec `GET /v1/testimonials`.
5. Afficher le détail d'un projet au clic avec `GET /v1/projects/{id}`.

Note : aucune route publique n'expose actuellement les catégories, services, partenaires ou statistiques. Si le site public doit les afficher séparément, il faudra valider une évolution backend.

### Côté admin

1. Se connecter avec `POST /v1/auth/login`.
2. Stocker le token Sanctum reçu.
3. Ajouter `Authorization: Bearer <token>` aux appels admin.
4. Charger les catégories et tags.
5. Créer les services et associer leurs tags via `tag_ids`.
6. Créer les clients.
7. Créer les projets avec une catégorie existante et éventuellement un service.
8. Créer les témoignages liés aux clients et projets.
9. Gérer les partenaires, visions et statistiques.
10. Se déconnecter avec `POST /v1/auth/logout`.

## 10. Exemples Angular

Service Angular indicatif :

```ts
import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Observable } from 'rxjs';

@Injectable({ providedIn: 'root' })
export class ApiService {
  private readonly baseUrl = 'http://localhost:8000/api';

  constructor(private http: HttpClient) {}

  private authHeaders(token: string): HttpHeaders {
    return new HttpHeaders({
      Accept: 'application/json',
      Authorization: `Bearer ${token}`,
    });
  }

  getProjects(): Observable<any> {
    return this.http.get(`${this.baseUrl}/v1/projects`, {
      headers: new HttpHeaders({ Accept: 'application/json' }),
    });
  }

  login(payload: { telephone: string; password: string }): Observable<any> {
    return this.http.post(`${this.baseUrl}/v1/auth/login`, payload, {
      headers: new HttpHeaders({
        Accept: 'application/json',
        'Content-Type': 'application/json',
      }),
    });
  }

  createClient(token: string, payload: any): Observable<any> {
    return this.http.post(`${this.baseUrl}/v1/admin/clients`, payload, {
      headers: this.authHeaders(token).set('Content-Type', 'application/json'),
    });
  }

  createPartnerWithLogo(token: string, payload: any, logoFile: File): Observable<any> {
    const formData = new FormData();
    formData.append('name', payload.name);

    if (payload.acronym) formData.append('acronym', payload.acronym);
    if (payload.domain) formData.append('domain', payload.domain);
    if (payload.description) formData.append('description', payload.description);

    formData.append('logo', logoFile);

    return this.http.post(`${this.baseUrl}/v1/admin/partners`, formData, {
      headers: this.authHeaders(token),
    });
  }

  updateServiceTags(token: string, serviceId: number, tagIds: number[]): Observable<any> {
    return this.http.patch(
      `${this.baseUrl}/v1/admin/services/${serviceId}`,
      { tag_ids: tagIds },
      { headers: this.authHeaders(token).set('Content-Type', 'application/json') }
    );
  }
}
```

Exemple d'upload service avec image et tableaux :

```ts
const formData = new FormData();
formData.append('title', 'Développement web');
formData.append('short_description', 'Création d applications web modernes.');
formData.append('description', 'Service complet de développement web.');
formData.append('icon', 'code');
formData.append('image', imageFile);

benefits.forEach((benefit, index) => {
  formData.append(`benefits[${index}]`, benefit);
});

tagIds.forEach((tagId, index) => {
  formData.append(`tag_ids[${index}]`, String(tagId));
});
```

Pour une mise à jour multipart en Angular, utiliser une surcharge de méthode si nécessaire :

```ts
formData.append('_method', 'PATCH');

this.http.post(`${baseUrl}/v1/admin/services/${serviceId}`, formData, {
  headers: authHeaders,
});
```

Cette approche est utile lorsque le client ou l'environnement gère mal les fichiers avec `PATCH` multipart.

## 11. Notes importantes

- Les routes admin nécessitent Sanctum.
- Les routes publiques actuelles ne nécessitent pas de token.
- Les uploads doivent utiliser `FormData`.
- Ne pas définir manuellement `Content-Type: multipart/form-data` dans Angular.
- Les tags des services sont synchronisés via `tag_ids`.
- Les réponses contiennent généralement `status`, `message`, `data`.
- Les erreurs métier contiennent généralement `status`, `message`, `error`.
- Les suppressions retournent généralement un message de succès sans objet métier.
- Les champs `created_by` et `updated_by` sont renseignés automatiquement côté backend pour les routes admin.
- Les réponses admin chargent souvent les relations d'audit `createdBy` et `updatedBy`.
- Les routes `GET /v1/admin/categories/{id}/category` et `GET /v1/admin/tags/{id}/tag` changent le statut.
- Ne pas appeler de route non déclarée dans `route:list`.

## 12. Limites ou incohérences détectées

- Aucune route publique `GET /v1/statistics` n'est actuellement déclarée. Les statistiques existent seulement en CRUD admin protégé.
- Aucune route publique dédiée aux partenaires, catégories, tags ou services n'est actuellement déclarée.
- Le test `tests/Feature/ServiceManagementTest.php` contient encore des appels à `/api/v1/admin/services/{id}/tags` et `/api/v1/admin/services/{id}/tags/{tagId}`. Ces routes ne sont pas présentes dans les fichiers de routes actuels; le test semble donc obsolète par rapport à la logique actuelle basée sur `tag_ids`.
- Les FormRequests `CategoryRequest` et `TagRequest` rendent `libelle` obligatoire même en `PUT/PATCH`, contrairement aux FormRequests du module Website qui autorisent les mises à jour partielles.
- Les méthodes `update` des contrôleurs catégories et tags retournent maintenant la ressource mise à jour; l'ancienne incohérence du booléen retourné par `$model->update($data)` a été corrigée.
- Les routes admin partenaires, statistiques, catégories, tags et auth existent, mais les tests Feature présents ne couvrent pas toutes ces ressources de manière visible dans le dépôt actuel.
- La route Laravel par défaut `GET /api/user` existe et est protégée par Sanctum, mais elle n'appartient pas au workflow `/v1` documenté ici.
