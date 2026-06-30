# Documentation API — Module Jobs

> Base URL : `http://localhost:8000/api`
> Authentification : Bearer Token (Laravel Sanctum) — requis sur toutes les routes `/admin/*`

---

## Table des matières

1. [Vue d'ensemble](#vue-densemble)
2. [Authentification](#authentification)
3. [Workflow Angular](#workflow-angular)
4. [Endpoints Publics](#endpoints-publics)
   - [Offres d'emploi (Job Openings)](#offres-demploi---public)
   - [Candidatures (Applications)](#candidatures---public)
   - [Moments Développeurs](#moments-développeurs---public)
   - [Héros (Heroes)](#héros---public)
   - [Pages](#pages---public)
   - [Newsletter](#newsletter---public)
   - [Devis (Quotes)](#devis---public)
5. [Endpoints Admin](#endpoints-admin)
   - [Offres d'emploi](#offres-demploi---admin)
   - [Candidatures](#candidatures---admin)
   - [Processus de Candidature](#processus-de-candidature---admin)
   - [Moments Développeurs](#moments-développeurs---admin)
   - [Héros](#héros---admin)
   - [Pages](#pages---admin)
   - [Newsletter](#newsletter---admin)
   - [Devis](#devis---admin)
6. [Modèles de Données](#modèles-de-données)
7. [Codes d'erreur](#codes-derreur)

---

## Vue d'ensemble

Le module **Jobs** gère :
- Les offres d'emploi et les candidatures associées
- Le suivi du processus de recrutement (pipeline)
- Les sections éditoriales du site (pages, héros, moments développeurs)
- Les abonnements newsletter
- Les demandes de devis

### Statuts disponibles

| Entité | Statuts |
|--------|---------|
| Job Application | `pending` `reviewed` `accepted` `rejected` |
| Application Process | `pending` `in_progress` `completed` |
| Quote | `pending` `in_progress` `approved` `rejected` |

---

## Authentification

Les routes admin nécessitent un Bearer Token obtenu lors de la connexion.

```typescript
// Angular : HttpInterceptor recommandé
headers: {
  'Authorization': `Bearer ${token}`,
  'Accept': 'application/json',
  'Content-Type': 'application/json'
}
```

> Les routes publiques (`/api/jobs/*`) n'exigent **aucun token**.

---

## Workflow Angular

### Architecture des services suggérée

```
src/app/
├── core/
│   └── services/
│       └── jobs/
│           ├── job-opening.service.ts       # Offres d'emploi
│           ├── job-application.service.ts   # Candidatures
│           ├── application-process.service.ts
│           ├── developer-moment.service.ts
│           ├── hero.service.ts
│           ├── page.service.ts
│           ├── newsletter.service.ts
│           └── quote.service.ts
```

### Flux utilisateur public (site vitrine)

```
Page Carrières
  │
  ├── GET /api/jobs/pages          → Récupère la page "Carrières" + ses héros
  ├── GET /api/jobs/heroes         → Affiche le hero banner
  ├── GET /api/jobs/openings       → Liste les offres actives
  │
  └── Clic sur une offre
        ├── GET /api/jobs/openings/{id}   → Détail de l'offre
        └── POST /api/jobs/applications   → Soumission de candidature (FormData)

Page Accueil
  ├── GET /api/jobs/developer-moments  → Témoignages équipe
  ├── POST /api/jobs/newsletters       → Inscription newsletter
  └── POST /api/jobs/quotes            → Demande de devis
```

### Flux admin (back-office)

```
Dashboard Admin
  │
  ├── Gestion Offres          /api/admin/jobs/openings          (CRUD)
  ├── Gestion Candidatures    /api/admin/jobs/applications      (Read + Update statut)
  │     └── Pipeline          /api/admin/jobs/application-processes (CRUD)
  ├── Gestion Pages           /api/admin/jobs/pages             (CRUD)
  ├── Gestion Héros           /api/admin/jobs/heroes            (CRUD)
  ├── Moments Développeurs    /api/admin/jobs/developer-moments (CRUD)
  ├── Newsletter              /api/admin/jobs/newsletters       (Read + Delete)
  └── Devis                   /api/admin/jobs/quotes            (Read + Update statut)
```

### Gestion des fichiers (multipart/form-data)

Pour les endpoints avec upload de fichiers, utiliser `FormData` dans Angular :

```typescript
const formData = new FormData();
formData.append('title', 'Titre');
formData.append('image', file); // File object

this.http.post('/api/admin/jobs/openings', formData);
// Ne PAS définir Content-Type manuellement — le navigateur le gère
```

---

## Endpoints Publics

### Offres d'emploi — Public

#### `GET /api/jobs/openings`

Liste toutes les offres actives (`is_active = true`).

**Response `200 OK`**
```json
{
  "data": [
    {
      "id": 1,
      "title": "Développeur Full-Stack",
      "slug": "developpeur-full-stack",
      "short_description": "Rejoignez notre équipe tech.",
      "description": "<p>Description complète...</p>",
      "skills": ["Laravel", "Angular", "MySQL"],
      "image": "job-openings/images/abc123.jpg",
      "closing_date": "2026-08-31",
      "is_active": true,
      "applications": [],
      "created_at": "2026-06-01 10:00:00"
    }
  ]
}
```

---

#### `GET /api/jobs/openings/{id}`

Détail d'une offre active.

**Paramètres URL**
| Paramètre | Type | Description |
|-----------|------|-------------|
| `id` | integer | ID de l'offre |

**Response `200 OK`**
```json
{
  "data": {
    "id": 1,
    "title": "Développeur Full-Stack",
    "slug": "developpeur-full-stack",
    "short_description": "Rejoignez notre équipe tech.",
    "description": "<p>Description complète...</p>",
    "skills": ["Laravel", "Angular", "MySQL"],
    "image": "job-openings/images/abc123.jpg",
    "closing_date": "2026-08-31",
    "is_active": true,
    "applications": [],
    "created_at": "2026-06-01 10:00:00"
  }
}
```

---

### Candidatures — Public

#### `POST /api/jobs/applications`

Soumettre une candidature. Accepte `multipart/form-data`.

**Request Body** (`multipart/form-data`)
| Champ | Type | Requis | Description |
|-------|------|--------|-------------|
| `job_opening_id` | integer | Oui | ID de l'offre |
| `last_name` | string (max:255) | Oui | Nom de famille |
| `first_name` | string (max:255) | Oui | Prénom |
| `email` | string (email) | Oui | Email — unique par offre |
| `phone` | string (max:50) | Non | Téléphone |
| `cv_file` | file (pdf,doc,docx, max:5MB) | Non | CV en fichier |
| `drive_link` | string (url) | Non | Lien Google Drive ou autre |

> Un même email ne peut postuler qu'une seule fois à la même offre.
> Le candidat peut fournir soit `cv_file`, soit `drive_link`.

**Response `201 Created`**
```json
{
  "data": {
    "id": 42,
    "job_opening_id": 1,
    "last_name": "Diallo",
    "first_name": "Mamadou",
    "email": "mamadou@example.com",
    "phone": "+224620000000",
    "cv_file": "job-applications/cv/cv_mamadou.pdf",
    "drive_link": null,
    "status": false,
    "application_status": "pending",
    "job_opening": { "...": "..." },
    "processes": [],
    "created_at": "2026-06-29 14:30:00"
  }
}
```

**Response `422 Unprocessable Entity`** — email déjà utilisé pour cette offre
```json
{
  "message": "The email has already been taken.",
  "errors": {
    "email": ["The email has already been taken."]
  }
}
```

---

### Moments Développeurs — Public

#### `GET /api/jobs/developer-moments`

Liste les témoignages actifs de l'équipe.

**Response `200 OK`**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Aminata Camara",
      "photo": "developer-moments/photos/aminata.jpg",
      "position": "Lead Developer",
      "quote": "Travailler ici est une aventure quotidienne.",
      "description": "Aminata nous partage son expérience...",
      "is_active": true,
      "created_at": "2026-05-15 09:00:00"
    }
  ]
}
```

---

#### `GET /api/jobs/developer-moments/{id}`

**Response `200 OK`** — même structure que l'élément ci-dessus.

---

### Héros — Public

#### `GET /api/jobs/heroes`

Liste tous les héros actifs avec leur page associée.

**Response `200 OK`**
```json
{
  "data": [
    {
      "id": 1,
      "page_id": 2,
      "title": "Construisons l'avenir ensemble",
      "sub_description": "Rejoignez une équipe passionnée.",
      "file": "heroes/files/banner.jpg",
      "is_active": true,
      "page": {
        "id": 2,
        "label": "Carrières",
        "link": "/carrieres",
        "description": null,
        "is_active": true,
        "heroes": [],
        "created_at": "2026-04-01 08:00:00"
      },
      "created_at": "2026-04-10 08:00:00"
    }
  ]
}
```

---

#### `GET /api/jobs/heroes/{id}`

**Response `200 OK`** — même structure que l'élément ci-dessus.

---

### Pages — Public

#### `GET /api/jobs/pages`

Liste les pages actives avec leurs héros.

**Response `200 OK`**
```json
{
  "data": [
    {
      "id": 1,
      "label": "Accueil",
      "link": "/",
      "description": "Page d'accueil du site.",
      "is_active": true,
      "heroes": [
        {
          "id": 1,
          "page_id": 1,
          "title": "Bienvenue",
          "sub_description": "Slogan de la société",
          "file": "heroes/files/home.jpg",
          "is_active": true,
          "page": null,
          "created_at": "2026-04-10 08:00:00"
        }
      ],
      "created_at": "2026-04-01 08:00:00"
    }
  ]
}
```

---

#### `GET /api/jobs/pages/{id}`

**Response `200 OK`** — même structure que l'élément ci-dessus.

---

### Newsletter — Public

#### `POST /api/jobs/newsletters`

S'abonner à la newsletter.

**Request Body** (`application/json`)
| Champ | Type | Requis | Description |
|-------|------|--------|-------------|
| `email` | string (email) | Oui | Unique dans la table newsletters |
| `name` | string (max:255) | Non | Nom de l'abonné |
| `phone` | string (max:50) | Non | Téléphone |

```json
{
  "email": "contact@example.com",
  "name": "Jean Dupont",
  "phone": "+33612345678"
}
```

**Response `201 Created`**
```json
{
  "data": {
    "id": 10,
    "name": "Jean Dupont",
    "phone": "+33612345678",
    "email": "contact@example.com",
    "is_subscribed": true,
    "created_at": "2026-06-29 12:00:00"
  }
}
```

**Response `422`** — email déjà inscrit
```json
{
  "errors": {
    "email": ["The email has already been taken."]
  }
}
```

---

### Devis — Public

#### `POST /api/jobs/quotes`

Soumettre une demande de devis.

**Request Body** (`application/json`)
| Champ | Type | Requis | Description |
|-------|------|--------|-------------|
| `project_name` | string (max:255) | Oui | Nom du projet |
| `description` | string | Oui | Description du besoin |
| `full_name` | string (max:255) | Oui | Nom complet du demandeur |
| `email` | string (email) | Oui | Email de contact |
| `phone` | string (max:50) | Oui | Téléphone |
| `estimated_budget` | string (max:255) | Non | Budget estimé (ex: "5000-10000 EUR") |
| `expected_deadline` | string (max:255) | Non | Délai souhaité (ex: "3 mois") |
| `company` | string (max:255) | Non | Entreprise |

```json
{
  "project_name": "Application mobile RH",
  "description": "Nous souhaitons développer une app mobile...",
  "full_name": "Ousmane Bah",
  "email": "obah@company.com",
  "phone": "+224625000000",
  "estimated_budget": "10000-20000 EUR",
  "expected_deadline": "6 mois",
  "company": "Tech Corp"
}
```

**Response `201 Created`**
```json
{
  "data": {
    "id": 5,
    "project_name": "Application mobile RH",
    "description": "Nous souhaitons développer une app mobile...",
    "estimated_budget": "10000-20000 EUR",
    "expected_deadline": "6 mois",
    "full_name": "Ousmane Bah",
    "email": "obah@company.com",
    "phone": "+224625000000",
    "company": "Tech Corp",
    "status": "pending",
    "is_approved": false,
    "created_at": "2026-06-29 14:00:00"
  }
}
```

---

## Endpoints Admin

> Toutes les routes admin nécessitent : `Authorization: Bearer {token}`

---

### Offres d'emploi — Admin

#### `GET /api/admin/jobs/openings`

Liste toutes les offres (actives et inactives) avec les candidatures.

**Response `200 OK`**
```json
{
  "data": [
    {
      "id": 1,
      "title": "Développeur Full-Stack",
      "slug": "developpeur-full-stack",
      "short_description": "Rejoignez notre équipe tech.",
      "description": "<p>Description...</p>",
      "skills": ["Laravel", "Angular"],
      "image": "job-openings/images/abc123.jpg",
      "closing_date": "2026-08-31",
      "is_active": true,
      "applications": [
        { "id": 1, "...": "..." }
      ],
      "created_at": "2026-06-01 10:00:00"
    }
  ]
}
```

---

#### `POST /api/admin/jobs/openings`

Créer une offre d'emploi. Accepte `multipart/form-data`.

**Request Body** (`multipart/form-data`)
| Champ | Type | Requis | Description |
|-------|------|--------|-------------|
| `title` | string (max:255) | Oui | Titre du poste |
| `slug` | string (max:255) | Oui | Identifiant URL unique |
| `short_description` | string (max:255) | Oui | Description courte |
| `description` | string | Non | Description longue (HTML) |
| `skills` | array of strings | Non | Compétences requises |
| `image` | file (jpg,jpeg,png,webp, max:2MB) | Non | Image de l'offre |
| `closing_date` | date (YYYY-MM-DD) | Non | Date de clôture |
| `is_active` | boolean | Non | Visible publiquement (défaut: true) |

```typescript
// Angular FormData
const form = new FormData();
form.append('title', 'Développeur Full-Stack');
form.append('slug', 'developpeur-full-stack');
form.append('short_description', 'Rejoignez notre équipe');
form.append('skills[]', 'Laravel');
form.append('skills[]', 'Angular');
form.append('closing_date', '2026-08-31');
form.append('is_active', '1');
if (imageFile) form.append('image', imageFile);
```

**Response `201 Created`**
```json
{
  "data": {
    "id": 2,
    "title": "Développeur Full-Stack",
    "slug": "developpeur-full-stack",
    "short_description": "Rejoignez notre équipe",
    "description": null,
    "skills": ["Laravel", "Angular"],
    "image": "job-openings/images/generated_name.jpg",
    "closing_date": "2026-08-31",
    "is_active": true,
    "applications": [],
    "created_at": "2026-06-29 15:00:00"
  }
}
```

---

#### `GET /api/admin/jobs/openings/{id}`

**Response `200 OK`** — même structure que le listing, avec `applications` chargées.

---

#### `PUT /api/admin/jobs/openings/{id}`

Mettre à jour une offre. Accepte `multipart/form-data` (tous les champs optionnels).

> Pour envoyer un PUT avec FormData en Angular, utiliser `POST` avec `_method: PUT` ou configurer le backend pour accepter `POST` en mise à jour.

**Request Body** (`multipart/form-data`) — tous les champs sont optionnels (`sometimes`)
| Champ | Type | Description |
|-------|------|-------------|
| `title` | string (max:255) | Nouveau titre |
| `slug` | string (max:255) | Nouveau slug (unique, ignore l'actuel) |
| `short_description` | string (max:255) | Courte description |
| `description` | string | Description longue |
| `skills` | array | Compétences |
| `image` | file (jpg,jpeg,png,webp, max:2MB) | Nouvelle image |
| `closing_date` | date | Date de clôture |
| `is_active` | boolean | Statut de visibilité |

**Response `200 OK`** — offre mise à jour.

---

#### `DELETE /api/admin/jobs/openings/{id}`

Supprimer une offre (et ses candidatures en cascade).

**Response `200 OK`**
```json
{
  "message": "Deleted successfully"
}
```

---

### Candidatures — Admin

#### `GET /api/admin/jobs/applications`

Liste toutes les candidatures avec l'offre associée et les processus de suivi.

**Response `200 OK`**
```json
{
  "data": [
    {
      "id": 42,
      "job_opening_id": 1,
      "last_name": "Diallo",
      "first_name": "Mamadou",
      "email": "mamadou@example.com",
      "phone": "+224620000000",
      "cv_file": "job-applications/cv/cv.pdf",
      "drive_link": null,
      "status": false,
      "application_status": "pending",
      "job_opening": {
        "id": 1,
        "title": "Développeur Full-Stack",
        "slug": "developpeur-full-stack",
        "...": "..."
      },
      "processes": [
        {
          "id": 1,
          "title": "Pré-sélection CV",
          "status": "completed",
          "is_completed": true,
          "processor": {
            "id": 3,
            "name": "Admin User",
            "email": "admin@company.com"
          },
          "processed_at": "2026-06-20 10:00:00",
          "created_at": "2026-06-19 08:00:00"
        }
      ],
      "created_at": "2026-06-15 14:30:00"
    }
  ]
}
```

---

#### `GET /api/admin/jobs/applications/{id}`

**Response `200 OK`** — même structure que l'élément ci-dessus.

---

#### `PUT /api/admin/jobs/applications/{id}`

Mettre à jour le statut d'une candidature. Accepte `multipart/form-data` ou `application/json`.

**Request Body** — tous les champs optionnels
| Champ | Type | Description |
|-------|------|-------------|
| `status` | enum | `pending` `reviewed` `accepted` `rejected` |
| `last_name` | string (max:255) | Modifier le nom |
| `first_name` | string (max:255) | Modifier le prénom |
| `email` | string (email) | Modifier l'email |
| `phone` | string (max:50) | Modifier le téléphone |
| `cv_file` | file (pdf,doc,docx, max:5MB) | Remplacer le CV |
| `drive_link` | string (url) | Lien Drive |

```json
{
  "status": "reviewed"
}
```

**Response `200 OK`** — candidature mise à jour.

---

#### `DELETE /api/admin/jobs/applications/{id}`

**Response `200 OK`**
```json
{
  "message": "Deleted successfully"
}
```

---

### Processus de Candidature — Admin

> Pipeline de suivi du recrutement pour chaque candidature.

#### `GET /api/admin/jobs/application-processes`

Liste tous les processus avec les candidatures et les utilisateurs responsables.

**Response `200 OK`**
```json
{
  "data": [
    {
      "id": 1,
      "job_application_id": 42,
      "title": "Pré-sélection CV",
      "description": "Vérification des compétences requises.",
      "status": "completed",
      "is_completed": true,
      "processed_by": 3,
      "processed_at": "2026-06-20 10:00:00",
      "processor": {
        "id": 3,
        "name": "Admin User",
        "email": "admin@company.com"
      },
      "created_at": "2026-06-19 08:00:00"
    }
  ]
}
```

---

#### `POST /api/admin/jobs/application-processes`

Créer une étape de processus pour une candidature.

**Request Body** (`application/json`)
| Champ | Type | Requis | Description |
|-------|------|--------|-------------|
| `job_application_id` | integer | Oui | ID de la candidature |
| `title` | string (max:255) | Oui | Titre de l'étape |
| `description` | string | Non | Détails de l'étape |
| `status` | enum | Non | `pending` `in_progress` `completed` (défaut: pending) |
| `processed_by` | integer | Non | ID utilisateur responsable (auto: user connecté si vide) |
| `processed_at` | datetime | Non | Date de traitement |

```json
{
  "job_application_id": 42,
  "title": "Entretien technique",
  "description": "Test technique de 2h en Laravel/Angular",
  "status": "pending",
  "processed_at": "2026-07-05 09:00:00"
}
```

> Si `processed_by` est omis, le système assigne automatiquement l'utilisateur connecté.

**Response `201 Created`**
```json
{
  "data": {
    "id": 2,
    "job_application_id": 42,
    "title": "Entretien technique",
    "description": "Test technique de 2h en Laravel/Angular",
    "status": "pending",
    "is_completed": false,
    "processed_by": 3,
    "processed_at": "2026-07-05 09:00:00",
    "processor": {
      "id": 3,
      "name": "Admin User",
      "email": "admin@company.com"
    },
    "created_at": "2026-06-29 16:00:00"
  }
}
```

---

#### `GET /api/admin/jobs/application-processes/{id}`

**Response `200 OK`** — même structure que l'élément ci-dessus.

---

#### `PUT /api/admin/jobs/application-processes/{id}`

Mettre à jour une étape (ex: marquer comme terminée).

**Request Body** (`application/json`) — tous les champs optionnels
| Champ | Type | Description |
|-------|------|-------------|
| `title` | string (max:255) | Titre |
| `description` | string | Détails |
| `status` | enum | `pending` `in_progress` `completed` |
| `processed_by` | integer | Responsable |
| `processed_at` | datetime | Date de traitement |

```json
{
  "status": "completed",
  "processed_at": "2026-07-05 11:30:00"
}
```

**Response `200 OK`** — processus mis à jour.

---

#### `DELETE /api/admin/jobs/application-processes/{id}`

**Response `200 OK`**
```json
{
  "message": "Deleted successfully"
}
```

---

### Moments Développeurs — Admin

#### `GET /api/admin/jobs/developer-moments`

Liste tous les moments (actifs et inactifs).

**Response `200 OK`**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Aminata Camara",
      "photo": "developer-moments/photos/aminata.jpg",
      "position": "Lead Developer",
      "quote": "Travailler ici est une aventure quotidienne.",
      "description": "Description longue...",
      "is_active": true,
      "created_at": "2026-05-15 09:00:00"
    }
  ]
}
```

---

#### `POST /api/admin/jobs/developer-moments`

Créer un témoignage. Accepte `multipart/form-data`.

**Request Body** (`multipart/form-data`)
| Champ | Type | Requis | Description |
|-------|------|--------|-------------|
| `name` | string (max:255) | Oui | Nom du développeur |
| `photo` | file (jpg,jpeg,png,webp, max:2MB) | Non | Photo |
| `position` | string (max:255) | Non | Poste occupé |
| `quote` | string | Non | Citation courte |
| `description` | string | Non | Description longue |
| `is_active` | boolean | Non | Visibilité (défaut: true) |

**Response `201 Created`** — même structure que le listing.

---

#### `PUT /api/admin/jobs/developer-moments/{id}`

Tous les champs optionnels (`sometimes`). Même structure que le `POST`.

---

#### `DELETE /api/admin/jobs/developer-moments/{id}`

**Response `200 OK`** — suppression confirmée.

---

### Héros — Admin

#### `GET /api/admin/jobs/heroes`

Liste tous les héros avec leur page associée.

**Response `200 OK`**
```json
{
  "data": [
    {
      "id": 1,
      "page_id": 2,
      "title": "Construisons l'avenir ensemble",
      "sub_description": "Rejoignez une équipe passionnée.",
      "file": "heroes/files/banner.jpg",
      "is_active": true,
      "page": {
        "id": 2,
        "label": "Carrières",
        "link": "/carrieres",
        "description": null,
        "is_active": true,
        "heroes": [],
        "created_at": "2026-04-01 08:00:00"
      },
      "created_at": "2026-04-10 08:00:00"
    }
  ]
}
```

---

#### `POST /api/admin/jobs/heroes`

Créer un héro. Accepte `multipart/form-data`.

**Request Body** (`multipart/form-data`)
| Champ | Type | Requis | Description |
|-------|------|--------|-------------|
| `page_id` | integer | Oui | ID de la page associée |
| `title` | string (max:255) | Oui | Titre principal |
| `sub_description` | string | Non | Sous-titre / description |
| `file` | file (jpg,jpeg,png,webp,mp4,pdf, max:10MB) | Non | Image, vidéo ou PDF |
| `is_active` | boolean | Non | Visibilité (défaut: true) |

**Response `201 Created`** — même structure que le listing.

---

#### `PUT /api/admin/jobs/heroes/{id}`

Tous les champs optionnels. Même structure que le `POST`.

---

#### `DELETE /api/admin/jobs/heroes/{id}`

**Response `200 OK`** — suppression confirmée.

---

### Pages — Admin

#### `GET /api/admin/jobs/pages`

Liste toutes les pages avec leurs héros.

**Response `200 OK`**
```json
{
  "data": [
    {
      "id": 1,
      "label": "Accueil",
      "link": "/",
      "description": "Page principale du site.",
      "is_active": true,
      "heroes": [ { "...": "..." } ],
      "created_at": "2026-04-01 08:00:00"
    }
  ]
}
```

---

#### `POST /api/admin/jobs/pages`

**Request Body** (`application/json`)
| Champ | Type | Requis | Description |
|-------|------|--------|-------------|
| `label` | string (max:255) | Oui | Libellé de la page |
| `link` | string (max:255) | Non | URL relative (ex: `/carrieres`) |
| `description` | string | Non | Description |
| `is_active` | boolean | Non | Visibilité (défaut: true) |

```json
{
  "label": "Carrières",
  "link": "/carrieres",
  "description": "Rejoignez notre équipe.",
  "is_active": true
}
```

**Response `201 Created`** — même structure que le listing.

---

#### `PUT /api/admin/jobs/pages/{id}`

Même body que le `POST`. Tous les champs requis lors de la mise à jour (`required` non `sometimes`).

---

#### `DELETE /api/admin/jobs/pages/{id}`

> Supprime la page **et ses héros en cascade**.

**Response `200 OK`** — suppression confirmée.

---

### Newsletter — Admin

#### `GET /api/admin/jobs/newsletters`

Liste tous les abonnés (actifs et désabonnés).

**Response `200 OK`**
```json
{
  "data": [
    {
      "id": 10,
      "name": "Jean Dupont",
      "phone": "+33612345678",
      "email": "contact@example.com",
      "is_subscribed": true,
      "created_at": "2026-06-29 12:00:00"
    }
  ]
}
```

---

#### `GET /api/admin/jobs/newsletters/{id}`

**Response `200 OK`** — même structure que l'élément ci-dessus.

---

#### `DELETE /api/admin/jobs/newsletters/{id}`

**Response `200 OK`** — suppression confirmée.

> Note : Il n'existe pas d'endpoint admin pour créer ou modifier un abonnement. Les abonnements sont créés uniquement via le formulaire public.

---

### Devis — Admin

#### `GET /api/admin/jobs/quotes`

Liste toutes les demandes de devis.

**Response `200 OK`**
```json
{
  "data": [
    {
      "id": 5,
      "project_name": "Application mobile RH",
      "description": "Nous souhaitons développer...",
      "estimated_budget": "10000-20000 EUR",
      "expected_deadline": "6 mois",
      "full_name": "Ousmane Bah",
      "email": "obah@company.com",
      "phone": "+224625000000",
      "company": "Tech Corp",
      "status": "pending",
      "is_approved": false,
      "created_at": "2026-06-29 14:00:00"
    }
  ]
}
```

---

#### `GET /api/admin/jobs/quotes/{id}`

**Response `200 OK`** — même structure que l'élément ci-dessus.

---

#### `PUT /api/admin/jobs/quotes/{id}`

Mettre à jour un devis (généralement le statut).

**Request Body** (`application/json`) — tous les champs optionnels
| Champ | Type | Description |
|-------|------|-------------|
| `status` | enum | `pending` `in_progress` `approved` `rejected` |
| `project_name` | string (max:255) | Nom du projet |
| `description` | string | Description |
| `estimated_budget` | string (max:255) | Budget |
| `expected_deadline` | string (max:255) | Délai |
| `full_name` | string (max:255) | Nom du demandeur |
| `email` | string (email) | Email |
| `phone` | string (max:50) | Téléphone |
| `company` | string (max:255) | Entreprise |

```json
{
  "status": "approved"
}
```

**Response `200 OK`** — devis mis à jour.

---

#### `DELETE /api/admin/jobs/quotes/{id}`

**Response `200 OK`** — suppression confirmée.

---

## Modèles de Données

### JobOpening
```typescript
interface JobOpening {
  id: number;
  title: string;
  slug: string;
  short_description: string;
  description: string | null;
  skills: string[] | null;
  image: string | null;          // chemin relatif au storage
  closing_date: string | null;   // YYYY-MM-DD
  is_active: boolean;
  applications: JobApplication[];
  created_at: string;            // YYYY-MM-DD HH:mm:ss
}
```

### JobApplication
```typescript
interface JobApplication {
  id: number;
  job_opening_id: number;
  last_name: string;
  first_name: string;
  email: string;
  phone: string | null;
  cv_file: string | null;        // chemin relatif au storage
  drive_link: string | null;
  status: boolean;               // true si accepté
  application_status: 'pending' | 'reviewed' | 'accepted' | 'rejected';
  job_opening: JobOpening | null;
  processes: JobApplicationProcess[];
  created_at: string;
}
```

### JobApplicationProcess
```typescript
interface JobApplicationProcess {
  id: number;
  job_application_id: number;
  title: string;
  description: string | null;
  status: 'pending' | 'in_progress' | 'completed';
  is_completed: boolean;         // true si status === 'completed'
  processed_by: number | null;
  processed_at: string | null;   // YYYY-MM-DD HH:mm:ss
  processor: {
    id: number | null;
    name: string | null;
    email: string | null;
  };
  created_at: string;
}
```

### DeveloperMoment
```typescript
interface DeveloperMoment {
  id: number;
  name: string;
  photo: string | null;
  position: string | null;
  quote: string | null;
  description: string | null;
  is_active: boolean;
  created_at: string;
}
```

### Hero
```typescript
interface Hero {
  id: number;
  page_id: number;
  title: string;
  sub_description: string | null;
  file: string | null;           // image, vidéo ou PDF
  is_active: boolean;
  page: Page | null;
  created_at: string;
}
```

### Page
```typescript
interface Page {
  id: number;
  label: string;
  link: string | null;
  description: string | null;
  is_active: boolean;
  heroes: Hero[];
  created_at: string;
}
```

### Newsletter
```typescript
interface Newsletter {
  id: number;
  name: string | null;
  phone: string | null;
  email: string;
  is_subscribed: boolean;
  created_at: string;
}
```

### Quote
```typescript
interface Quote {
  id: number;
  project_name: string;
  description: string;
  estimated_budget: string | null;
  expected_deadline: string | null;
  full_name: string;
  email: string;
  phone: string;
  company: string | null;
  status: 'pending' | 'in_progress' | 'approved' | 'rejected';
  is_approved: boolean;
  created_at: string;
}
```

---

## Codes d'erreur

| Code | Signification | Cas courant |
|------|--------------|-------------|
| `200` | Succès | GET, PUT, DELETE réussis |
| `201` | Créé | POST réussi |
| `401` | Non authentifié | Token absent ou expiré sur route admin |
| `403` | Accès interdit | Permissions insuffisantes |
| `404` | Non trouvé | ID inexistant |
| `422` | Validation échouée | Champs manquants ou invalides |
| `500` | Erreur serveur | Erreur interne |

### Format d'erreur de validation (`422`)
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "champ": ["Message d'erreur 1", "Message d'erreur 2"]
  }
}
```

### Interception dans Angular
```typescript
// core/interceptors/error.interceptor.ts
intercept(req: HttpRequest<any>, next: HttpHandler): Observable<HttpEvent<any>> {
  return next.handle(req).pipe(
    catchError((error: HttpErrorResponse) => {
      switch (error.status) {
        case 401: this.router.navigate(['/login']); break;
        case 422: this.notifyValidationErrors(error.error.errors); break;
        case 404: this.router.navigate(['/404']); break;
      }
      return throwError(() => error);
    })
  );
}
```

---

*Documentation générée le 29/06/2026 — Module Jobs v1.0*
