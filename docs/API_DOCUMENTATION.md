# API Documentation

Generated from the current Laravel source code on `2026-06-30`.

## Overview

- Base API prefix: `/api`
- Main frontend prefixes in use:
    - `/api/v1/*`
    - `/api/jobs/*`
    - `/api/admin/jobs/*`
- Auth mechanism for protected routes: `Authorization: Bearer <sanctum_token>`
- Protected routes are explicitly guarded with `auth:sanctum`
- When multiple endpoints return the exact same controller/resource contract, the first endpoint in that group contains the full JSON block and the following endpoints explicitly reuse that canonical example to keep the file maintainable.

## Global Response Shapes

### Standard Success

```json
{
    "status": 1,
    "message": "Operation completed successfully.",
    "data": {}
}
```

### Success With Token

```json
{
    "status": 1,
    "data": {},
    "token": "1|plain-text-token",
    "message": "Authenticated successfully."
}
```

### Standard Error

```json
{
    "status": 0,
    "message": "Resource not found.",
    "error": []
}
```

### No-Content Success

```json
{
    "status": 1,
    "message": "Deleted successfully."
}
```

### Validation Error

Laravel validation failures are not wrapped by the custom trait. They follow the default Laravel `422` shape:

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "email": ["The email field is required."],
        "password": ["The password confirmation does not match."]
    }
}
```

## Frontend Notes

- Use `application/json` for regular payloads.
- Use `multipart/form-data` for endpoints that send files:
    - auth register with `avatar`
    - articles with `cover`
    - partners with `logo`
    - services with `image`
    - jobs developer moments with `photo`
    - jobs heroes with `file`
    - public job applications with `cv_file`
- Resource-based responses usually format dates as `d-m-Y H:i:s`.
- Raw Eloquent responses usually serialize dates as ISO timestamps.
- Some raw admin responses load audit relations; in JSON those usually appear as nested `created_by` / `updated_by` user objects.
- Current implementation quirk:
    - `PUT/PATCH /api/v1/admin/categories/{category}` returns `data: true` instead of the updated category object.
    - `PUT/PATCH /api/v1/admin/tags/{tag}` returns `data: true` instead of the updated tag object.
- Canonical-example rule used in this document:
    - the first route in a CRUD group contains the full JSON example
    - sibling routes with the same response schema point back to that canonical example
    - delete and status-switch routes keep their own explicit response examples because their payloads differ

---

## Misc

### GET `/api/user`

- Auth: Bearer token required
- Payload example: none
- Success response example:

```json
{
    "id": 1,
    "name": "Admin User",
    "email": "admin@example.com",
    "telephone": "690000000",
    "status": true,
    "avatar": null,
    "avatar_url": "https://ui-avatars.com/api/?name=A+U&color=7F9CF5&background=EBF4FF",
    "created_at": "2026-06-30T08:00:00.000000Z",
    "updated_at": "2026-06-30T08:00:00.000000Z"
}
```

---

## Auth

### POST `/api/v1/auth/register`

- Auth: public
- Content-Type: `multipart/form-data`
- Payload example:

```json
{
    "name": "John Doe",
    "telephone": "690123456",
    "email": "john@example.com",
    "avatar": "<binary image file>",
    "password": "secret123",
    "password_confirmation": "secret123"
}
```

- Success response example:

```json
{
    "status": 1,
    "data": {
        "id": 12,
        "name": "John Doe",
        "telephone": "690123456",
        "email": "john@example.com",
        "avatar_url": "https://cdn.example.com/avatars/john-doe.jpg",
        "created_at": "30-06-2026 14:20:15",
        "updated_at": "30-06-2026 14:20:15"
    },
    "token": "12|sanctum-registration-token",
    "message": "Utilisateur créé avec succès."
}
```

### POST `/api/v1/auth/login`

- Auth: public
- Content-Type: `application/json`
- Payload example:

```json
{
    "telephone": "690123456",
    "password": "secret123"
}
```

- Success response example:

```json
{
    "status": 1,
    "data": {
        "id": 12,
        "name": "John Doe",
        "telephone": "690123456",
        "email": "john@example.com",
        "avatar_url": "https://cdn.example.com/avatars/john-doe.jpg",
        "created_at": "30-06-2026 14:20:15",
        "updated_at": "30-06-2026 14:20:15"
    },
    "token": "12|sanctum-login-token",
    "message": "Utilisateur connecté avec succès."
}
```

- Invalid credentials example:

```json
{
    "status": 0,
    "message": "Information invalide",
    "error": []
}
```

### POST `/api/v1/auth/logout`

- Auth: Bearer token required
- Payload example: none
- Success response example:

```json
{
    "status": 1,
    "message": "Utilisateur deconnecté avec succès."
}
```

---

## Blog

### GET `/api/v1/articles`

- Auth: public
- Payload example: none
- Success response example:

```json
{
    "status": 1,
    "message": "Liste des articles chargée avec succès.",
    "data": [
        {
            "id": 7,
            "title": "Launching a New Angular Frontend",
            "short_description": "How the SPA consumes the backend API.",
            "description": "Long article content...",
            "cover_url": "https://cdn.example.com/articles/angular-spa-cover.jpg",
            "tags": [
                {
                    "id": 2,
                    "libelle": "Frontend",
                    "description": "Frontend related content",
                    "status": true,
                    "created_at": "2026-06-22T12:00:00.000000Z",
                    "updated_at": "2026-06-22T12:00:00.000000Z"
                }
            ],
            "comments": [],
            "created_by": "Admin User",
            "updated_by": "Admin User",
            "created_at": "30-06-2026 10:00:00",
            "updated_at": "30-06-2026 10:00:00"
        }
    ]
}
```

### GET `/api/v1/articles/{id}`

- Auth: public
- Payload example: none
- Success response example:

```json
{
    "status": 1,
    "message": "Article chargé avec succès.",
    "data": {
        "id": 7,
        "title": "Launching a New Angular Frontend",
        "short_description": "How the SPA consumes the backend API.",
        "description": "Long article content...",
        "cover_url": "https://cdn.example.com/articles/angular-spa-cover.jpg",
        "tags": [
            {
                "id": 2,
                "libelle": "Frontend",
                "description": "Frontend related content",
                "status": true,
                "created_at": "2026-06-22T12:00:00.000000Z",
                "updated_at": "2026-06-22T12:00:00.000000Z"
            }
        ],
        "comments": [
            {
                "id": 15,
                "name": "Jane",
                "email": "jane@example.com",
                "content": "Very useful article.",
                "created_at": "30-06-2026 10:35:00"
            }
        ],
        "created_by": "Admin User",
        "updated_by": "Admin User",
        "created_at": "30-06-2026 10:00:00",
        "updated_at": "30-06-2026 10:00:00"
    }
}
```

### GET `/api/v1/articles/{article}/comments`

- Auth: public
- Payload example: none
- Success response example:

```json
{
    "status": 1,
    "message": "Liste des commentaires chargée avec succès.",
    "data": [
        {
            "id": 15,
            "name": "Jane",
            "email": "jane@example.com",
            "content": "Very useful article.",
            "created_at": "30-06-2026 10:35:00"
        }
    ]
}
```

### POST `/api/v1/articles/{article}/comments`

- Auth: public
- Content-Type: `application/json`
- Payload example:

```json
{
    "name": "Jane",
    "email": "jane@example.com",
    "content": "Very useful article."
}
```

- Success response example:

```json
{
    "status": 1,
    "message": "Commentaire créé avec succès.",
    "data": {
        "id": 15,
        "name": "Jane",
        "email": "jane@example.com",
        "content": "Very useful article.",
        "created_at": "30-06-2026 10:35:00"
    }
}
```

### POST `/api/v1/admin/articles`

- Auth: Bearer token required
- Content-Type: `multipart/form-data`
- Payload example:

```json
{
    "title": "Launching a New Angular Frontend",
    "short_description": "How the SPA consumes the backend API.",
    "description": "Long article content...",
    "cover": "<binary image file>",
    "tags": [2, 5]
}
```

- Success response example:

```json
{
    "status": 1,
    "message": "Article créé avec succès.",
    "data": {
        "id": 7,
        "title": "Launching a New Angular Frontend",
        "short_description": "How the SPA consumes the backend API.",
        "description": "Long article content...",
        "cover_url": "https://cdn.example.com/articles/angular-spa-cover.jpg",
        "tags": [
            {
                "id": 2,
                "libelle": "Frontend",
                "description": "Frontend related content",
                "status": true
            }
        ],
        "comments": [],
        "created_by": "Admin User",
        "updated_by": null,
        "created_at": "30-06-2026 10:00:00",
        "updated_at": "30-06-2026 10:00:00"
    }
}
```

### PUT `/api/v1/admin/articles/{id}`

- Auth: Bearer token required
- Content-Type: `multipart/form-data`
- Payload example:

```json
{
    "title": "Launching a New Angular Frontend v2",
    "short_description": "Updated summary for the frontend team.",
    "description": "Updated article content...",
    "cover": "<binary image file>",
    "tags": [2, 6]
}
```

- Success response example: same shape as article create, with updated values.

### DELETE `/api/v1/admin/articles/{id}`

- Auth: Bearer token required
- Payload example: none
- Success response example:

```json
{
    "status": 1,
    "message": "Article supprimé avec succès."
}
```

### DELETE `/api/v1/admin/comments/{id}`

- Auth: Bearer token required
- Payload example: none
- Success response example:

```json
{
    "status": 1,
    "message": "Commentaire supprimé avec succès."
}
```

---

## Contact

### POST `/api/v1/contacts`

- Auth: public
- Content-Type: `application/json`
- Payload example:

```json
{
    "name": "Alice Doe",
    "email": "alice@example.com",
    "phone": "+237690000001",
    "company": "ACME Ltd",
    "subject": "Need a quote",
    "message": "We need a custom web platform."
}
```

- Success response example:

```json
{
    "status": 1,
    "message": "Message envoyé avec succès.",
    "data": {
        "id": 4,
        "name": "Alice Doe",
        "email": "alice@example.com",
        "phone": "+237690000001",
        "company": "ACME Ltd",
        "subject": "Need a quote",
        "message": "We need a custom web platform.",
        "created_at": "30-06-2026 11:05:00"
    }
}
```

### GET `/api/v1/admin/contacts`

- Auth: Bearer token required
- Payload example: none
- Success response example:

```json
{
    "status": 1,
    "message": "Liste des messages chargée avec succès.",
    "data": [
        {
            "id": 4,
            "name": "Alice Doe",
            "email": "alice@example.com",
            "phone": "+237690000001",
            "company": "ACME Ltd",
            "subject": "Need a quote",
            "message": "We need a custom web platform.",
            "created_at": "30-06-2026 11:05:00"
        }
    ]
}
```

### GET `/api/v1/admin/contacts/{id}`

- Auth: Bearer token required
- Payload example: none
- Success response example: same shape as a single contact object from the list above.

### DELETE `/api/v1/admin/contacts/{id}`

- Auth: Bearer token required
- Payload example: none
- Success response example:

```json
{
    "status": 1,
    "message": "Contact supprimé avec succès."
}
```

---

## Offers

### GET `/api/v1/offers`

- Auth: public
- Payload example: none
- Success response example:

```json
{
    "status": 1,
    "message": "Liste des offres chargée avec succès.",
    "data": [
        {
            "id": 1,
            "plan": "Starter",
            "price": "99000.00",
            "features": ["Landing page", "Basic SEO"],
            "is_popular": false,
            "offer_type": {
                "id": 2,
                "name": "Website",
                "description": "Website offers",
                "created_at": "30-06-2026 09:00:00",
                "updated_at": "30-06-2026 09:00:00"
            },
            "created_at": "30-06-2026 09:15:00",
            "updated_at": "30-06-2026 09:15:00"
        }
    ]
}
```

### GET `/api/v1/offers/{id}`

- Auth: public
- Payload example: none
- Success response example: same shape as a single offer object from the list above.

### GET `/api/v1/admin/offer-types`

- Auth: Bearer token required
- Payload example: none
- Success response example:

```json
{
    "status": 1,
    "message": "Liste des types d'offres chargée avec succès.",
    "data": [
        {
            "id": 2,
            "name": "Website",
            "description": "Website offers",
            "created_at": "30-06-2026 09:00:00",
            "updated_at": "30-06-2026 09:00:00"
        }
    ]
}
```

### POST `/api/v1/admin/offer-types`

- Auth: Bearer token required
- Content-Type: `application/json`
- Payload example:

```json
{
    "name": "Website",
    "description": "Website offers"
}
```

- Success response example: same shape as a single offer type object above.

### GET `/api/v1/admin/offer-types/{offer_type}`

- Auth: Bearer token required
- Payload example: none
- Success response example: same shape as a single offer type object above.

### PUT/PATCH `/api/v1/admin/offer-types/{offer_type}`

- Auth: Bearer token required
- Content-Type: `application/json`
- Payload example:

```json
{
    "name": "Website & Portal",
    "description": "Updated offer type label"
}
```

- Success response example: same shape as a single offer type object above, with updated values.

### DELETE `/api/v1/admin/offer-types/{offer_type}`

- Auth: Bearer token required
- Payload example: none
- Success response example:

```json
{
    "status": 1,
    "message": "Type d'offre supprimé avec succès."
}
```

### POST `/api/v1/admin/offers`

- Auth: Bearer token required
- Content-Type: `application/json`
- Payload example:

```json
{
    "offer_type_id": 2,
    "plan": "Starter",
    "price": 99000,
    "features": ["Landing page", "Basic SEO"],
    "is_popular": false
}
```

- Success response example: same shape as a single offer object above.

### PUT `/api/v1/admin/offers/{id}`

- Auth: Bearer token required
- Content-Type: `application/json`
- Payload example:

```json
{
    "offer_type_id": 2,
    "plan": "Growth",
    "price": 199000,
    "features": ["Corporate website", "Analytics integration"],
    "is_popular": true
}
```

- Success response example: same shape as a single offer object above, with updated values.

### DELETE `/api/v1/admin/offers/{id}`

- Auth: Bearer token required
- Payload example: none
- Success response example:

```json
{
    "status": 1,
    "message": "Offre supprimée avec succès."
}
```

---

## Settings

### GET `/api/v1/admin/categories/{id}/category`

- Auth: Bearer token required
- Payload example: none
- Purpose: toggles the category `status`
- Success response example:

```json
{
    "status": 1,
    "message": "Statut de la catégorie modifié avec succès."
}
```

### GET `/api/v1/admin/categories`

- Auth: Bearer token required
- Payload example: none
- Success response example:

```json
{
    "status": 1,
    "message": "Liste des catégories chargée avec succès.",
    "data": [
        {
            "id": 1,
            "libelle": "Web Apps",
            "description": "Applications web sur mesure",
            "status": true,
            "created_at": "2026-06-22T11:49:35.000000Z",
            "updated_at": "2026-06-22T11:49:35.000000Z",
            "created_by": {
                "id": 1,
                "name": "Admin User",
                "email": "admin@example.com",
                "telephone": "690000000",
                "status": true,
                "avatar": null,
                "avatar_url": "https://ui-avatars.com/api/?name=A+U&color=7F9CF5&background=EBF4FF"
            },
            "updated_by": null
        }
    ]
}
```

### POST `/api/v1/admin/categories`

- Auth: Bearer token required
- Content-Type: `application/json`
- Payload example:

```json
{
    "libelle": "Web Apps",
    "description": "Applications web sur mesure"
}
```

- Success response example: same shape as a single category object above.

### GET `/api/v1/admin/categories/{category}`

- Auth: Bearer token required
- Payload example: none
- Success response example: same shape as a single category object above.

### PUT/PATCH `/api/v1/admin/categories/{category}`

- Auth: Bearer token required
- Content-Type: `application/json`
- Payload example:

```json
{
    "libelle": "Web Platforms",
    "description": "Updated category label"
}
```

- Current success response example:

```json
{
    "status": 1,
    "message": "Catégorie modifiée avec succès.",
    "data": true
}
```

### DELETE `/api/v1/admin/categories/{category}`

- Auth: Bearer token required
- Payload example: none
- Success response example:

```json
{
    "status": 1,
    "message": "Catégorie supprimée avec succès."
}
```

### GET `/api/v1/admin/tags/{id}/tag`

- Auth: Bearer token required
- Payload example: none
- Purpose: toggles the tag `status`
- Success response example:

```json
{
    "status": 1,
    "message": "Statut du tag modifié avec succès."
}
```

### GET `/api/v1/admin/tags`

- Auth: Bearer token required
- Payload example: none
- Success response example:

```json
{
    "status": 1,
    "message": "Liste des tags chargée avec succès.",
    "data": [
        {
            "id": 2,
            "libelle": "Frontend",
            "description": "Frontend related content",
            "status": true,
            "created_at": "2026-06-22T11:49:46.000000Z",
            "updated_at": "2026-06-22T11:49:46.000000Z",
            "created_by": {
                "id": 1,
                "name": "Admin User",
                "email": "admin@example.com",
                "telephone": "690000000",
                "status": true,
                "avatar": null,
                "avatar_url": "https://ui-avatars.com/api/?name=A+U&color=7F9CF5&background=EBF4FF"
            },
            "updated_by": null
        }
    ]
}
```

### POST `/api/v1/admin/tags`

- Auth: Bearer token required
- Content-Type: `application/json`
- Payload example:

```json
{
    "libelle": "Frontend",
    "description": "Frontend related content"
}
```

- Success response example: same shape as a single tag object above.

### GET `/api/v1/admin/tags/{tag}`

- Auth: Bearer token required
- Payload example: none
- Success response example: same shape as a single tag object above.

### PUT/PATCH `/api/v1/admin/tags/{tag}`

- Auth: Bearer token required
- Content-Type: `application/json`
- Payload example:

```json
{
    "libelle": "Frontend Angular",
    "description": "Updated tag label"
}
```

- Current success response example:

```json
{
    "status": 1,
    "message": "Tag modifié avec succès.",
    "data": true
}
```

### DELETE `/api/v1/admin/tags/{tag}`

- Auth: Bearer token required
- Payload example: none
- Success response example:

```json
{
    "status": 1,
    "message": "Tag supprimé avec succès."
}
```

---

## Website Public

### GET `/api/v1/projects`

- Auth: public
- Query parameters example:

```json
{
    "category_id": 1,
    "service_id": 3
}
```

- Success response example:

```json
{
    "status": 1,
    "message": "Liste des projets chargée avec succès.",
    "data": [
        {
            "id": 9,
            "category_id": 1,
            "service_id": 3,
            "title": "Corporate Portal",
            "short_description": "Private portal for internal staff.",
            "description": "Full project description...",
            "demo_link": "https://portal.example.com",
            "category": {
                "id": 1,
                "libelle": "Web Apps",
                "description": "Applications web sur mesure",
                "status": true
            },
            "service": {
                "id": 3,
                "icon": "globe",
                "image_path": "services/corporate-portal.jpg",
                "title": "Web Development",
                "short_description": "Custom web development",
                "description": "We build modern web platforms.",
                "benefits": ["Scalable architecture", "Angular friendly API"]
            }
        }
    ]
}
```

### GET `/api/v1/projects/{id}`

- Auth: public
- Payload example: none
- Success response example: same shape as a single public project object above.

### GET `/api/v1/testimonials`

- Auth: public
- Payload example: none
- Success response example:

```json
{
    "status": 1,
    "message": "Liste des témoignages chargée avec succès.",
    "data": [
        {
            "id": 5,
            "content": "Great collaboration from kickoff to delivery.",
            "client": {
                "id": 2,
                "first_name": "Sonia",
                "last_name": "Mballa",
                "job_title": "CEO"
            },
            "project": {
                "id": 9,
                "title": "Corporate Portal",
                "short_description": "Private portal for internal staff."
            }
        }
    ]
}
```

### GET `/api/v1/visions`

- Auth: public
- Payload example: none
- Success response example:

```json
{
    "status": 1,
    "message": "Liste des visions chargée avec succès.",
    "data": [
        {
            "id": 1,
            "title": "Build sustainable digital products",
            "description": "We focus on maintainable systems and measurable business value.",
            "author": "SPA TECH"
        }
    ]
}
```

---

## Website Admin

### Clients

#### GET `/api/v1/admin/clients`

- Auth: Bearer token required
- Payload example: none
- Success response example:

```json
{
    "status": 1,
    "message": "Liste des clients chargée avec succès.",
    "data": [
        {
            "id": 2,
            "first_name": "Sonia",
            "last_name": "Mballa",
            "job_title": "CEO",
            "created_at": "2026-06-23T11:46:11.000000Z",
            "updated_at": "2026-06-23T11:46:11.000000Z",
            "created_by": {
                "id": 1,
                "name": "Admin User",
                "email": "admin@example.com",
                "telephone": "690000000",
                "status": true,
                "avatar_url": "https://ui-avatars.com/api/?name=A+U&color=7F9CF5&background=EBF4FF"
            },
            "updated_by": null
        }
    ]
}
```

#### POST `/api/v1/admin/clients`

- Auth: Bearer token required
- Content-Type: `application/json`
- Payload example:

```json
{
    "first_name": "Sonia",
    "last_name": "Mballa",
    "job_title": "CEO"
}
```

- Success response example: same shape as a single client object above.

#### GET `/api/v1/admin/clients/{client}`

- Auth: Bearer token required
- Payload example: none
- Success response example: same shape as a single client object above.

#### PUT/PATCH `/api/v1/admin/clients/{client}`

- Auth: Bearer token required
- Content-Type: `application/json`
- Payload example:

```json
{
    "first_name": "Sonia",
    "last_name": "Mballa",
    "job_title": "Founder & CEO"
}
```

- Success response example: same shape as a single client object above, with updated values.

#### DELETE `/api/v1/admin/clients/{client}`

- Auth: Bearer token required
- Payload example: none
- Success response example:

```json
{
    "status": 1,
    "message": "Client supprimé avec succès."
}
```

### Partners

#### GET `/api/v1/admin/partners`

- Auth: Bearer token required
- Payload example: none
- Success response example:

```json
{
    "status": 1,
    "message": "Liste des partenaires chargée avec succès.",
    "data": [
        {
            "id": 3,
            "name": "Example Corp",
            "acronym": "EC",
            "domain": "Technology",
            "description": "Strategic integration partner.",
            "logo_path": "partners/example-corp.png",
            "logo_url": "https://cdn.example.com/partners/example-corp.png",
            "created_at": "2026-06-23T11:45:17.000000Z",
            "updated_at": "2026-06-23T11:45:17.000000Z",
            "created_by": {
                "id": 1,
                "name": "Admin User",
                "email": "admin@example.com",
                "telephone": "690000000",
                "status": true
            },
            "updated_by": null
        }
    ]
}
```

#### POST `/api/v1/admin/partners`

- Auth: Bearer token required
- Content-Type: `multipart/form-data`
- Payload example:

```json
{
    "name": "Example Corp",
    "acronym": "EC",
    "domain": "Technology",
    "description": "Strategic integration partner.",
    "logo": "<binary image file>"
}
```

- Success response example: same shape as a single partner object above.

#### GET `/api/v1/admin/partners/{partner}`

- Auth: Bearer token required
- Payload example: none
- Success response example: same shape as a single partner object above.

#### PUT/PATCH `/api/v1/admin/partners/{partner}`

- Auth: Bearer token required
- Content-Type: `multipart/form-data`
- Payload example:

```json
{
    "name": "Example Corp International",
    "acronym": "ECI",
    "domain": "Technology",
    "description": "Updated partner description.",
    "logo": "<binary image file>"
}
```

- Success response example: same shape as a single partner object above, with updated values.

#### DELETE `/api/v1/admin/partners/{partner}`

- Auth: Bearer token required
- Payload example: none
- Success response example:

```json
{
    "status": 1,
    "message": "Partenaire supprimé avec succès."
}
```

### Projects

#### GET `/api/v1/admin/projects`

- Auth: Bearer token required
- Payload example: none
- Success response example:

```json
{
    "status": 1,
    "message": "Liste des projets chargée avec succès.",
    "data": [
        {
            "id": 9,
            "category_id": 1,
            "service_id": 3,
            "title": "Corporate Portal",
            "short_description": "Private portal for internal staff.",
            "description": "Full project description...",
            "demo_link": "https://portal.example.com",
            "created_at": "2026-06-23T11:46:18.000000Z",
            "updated_at": "2026-06-23T11:46:18.000000Z",
            "category": {
                "id": 1,
                "libelle": "Web Apps",
                "description": "Applications web sur mesure",
                "status": true
            },
            "service": {
                "id": 3,
                "icon": "globe",
                "image_path": "services/corporate-portal.jpg",
                "image_url": "https://cdn.example.com/services/corporate-portal.jpg",
                "title": "Web Development",
                "short_description": "Custom web development",
                "description": "We build modern web platforms.",
                "benefits": ["Scalable architecture", "Angular friendly API"]
            },
            "created_by": {
                "id": 1,
                "name": "Admin User",
                "email": "admin@example.com"
            },
            "updated_by": null
        }
    ]
}
```

#### POST `/api/v1/admin/projects`

- Auth: Bearer token required
- Content-Type: `application/json`
- Payload example:

```json
{
    "category_id": 1,
    "service_id": 3,
    "title": "Corporate Portal",
    "short_description": "Private portal for internal staff.",
    "description": "Full project description...",
    "demo_link": "https://portal.example.com"
}
```

- Success response example: same shape as a single admin project object above.

#### GET `/api/v1/admin/projects/{project}`

- Auth: Bearer token required
- Payload example: none
- Success response example: same shape as a single admin project object above.

#### PUT/PATCH `/api/v1/admin/projects/{project}`

- Auth: Bearer token required
- Content-Type: `application/json`
- Payload example:

```json
{
    "category_id": 1,
    "service_id": 3,
    "title": "Corporate Portal v2",
    "short_description": "Updated project summary.",
    "description": "Updated project description...",
    "demo_link": "https://portal-v2.example.com"
}
```

- Success response example: same shape as a single admin project object above, with updated values.

#### DELETE `/api/v1/admin/projects/{project}`

- Auth: Bearer token required
- Payload example: none
- Success response example:

```json
{
    "status": 1,
    "message": "Projet supprimé avec succès."
}
```

### Services

#### GET `/api/v1/admin/services`

- Auth: Bearer token required
- Payload example: none
- Success response example:

```json
{
    "status": 1,
    "message": "Liste des services chargée avec succès.",
    "data": [
        {
            "id": 3,
            "icon": "globe",
            "image_path": "services/corporate-portal.jpg",
            "image_url": "https://cdn.example.com/services/corporate-portal.jpg",
            "title": "Web Development",
            "short_description": "Custom web development",
            "description": "We build modern web platforms.",
            "benefits": ["Scalable architecture", "Angular friendly API"],
            "created_at": "2026-06-23T11:45:48.000000Z",
            "updated_at": "2026-06-23T11:45:48.000000Z",
            "tags": [
                {
                    "id": 2,
                    "libelle": "Frontend",
                    "description": "Frontend related content",
                    "status": true
                }
            ],
            "created_by": {
                "id": 1,
                "name": "Admin User",
                "email": "admin@example.com"
            },
            "updated_by": null
        }
    ]
}
```

#### POST `/api/v1/admin/services`

- Auth: Bearer token required
- Content-Type: `multipart/form-data`
- Payload example:

```json
{
    "icon": "globe",
    "image": "<binary image file>",
    "title": "Web Development",
    "short_description": "Custom web development",
    "description": "We build modern web platforms.",
    "benefits": ["Scalable architecture", "Angular friendly API"],
    "tag_ids": [2, 4]
}
```

- Success response example: same shape as a single service object above.

#### GET `/api/v1/admin/services/{service}`

- Auth: Bearer token required
- Payload example: none
- Success response example: same shape as a single service object above.

#### PUT/PATCH `/api/v1/admin/services/{service}`

- Auth: Bearer token required
- Content-Type: `multipart/form-data`
- Payload example:

```json
{
    "icon": "code",
    "image": "<binary image file>",
    "title": "Web Engineering",
    "short_description": "Updated summary",
    "description": "Updated detailed description",
    "benefits": ["API-first delivery", "Maintainable backend"],
    "tag_ids": [2, 5]
}
```

- Success response example: same shape as a single service object above, with updated values.

#### DELETE `/api/v1/admin/services/{service}`

- Auth: Bearer token required
- Payload example: none
- Success response example:

```json
{
    "status": 1,
    "message": "Service supprimé avec succès."
}
```

### Statistics

#### GET `/api/v1/admin/statistics`

- Auth: Bearer token required
- Payload example: none
- Success response example:

```json
{
    "status": 1,
    "message": "Liste des statistiques chargée avec succès.",
    "data": [
        {
            "id": 1,
            "label": "Projects Delivered",
            "value": "120.00",
            "unit": "+",
            "created_at": "2026-06-23T11:45:58.000000Z",
            "updated_at": "2026-06-23T11:45:58.000000Z",
            "created_by": {
                "id": 1,
                "name": "Admin User",
                "email": "admin@example.com"
            },
            "updated_by": null
        }
    ]
}
```

#### POST `/api/v1/admin/statistics`

- Auth: Bearer token required
- Content-Type: `application/json`
- Payload example:

```json
{
    "label": "Projects Delivered",
    "value": 120,
    "unit": "+"
}
```

- Success response example: same shape as a single statistic object above.

#### GET `/api/v1/admin/statistics/{statistic}`

- Auth: Bearer token required
- Payload example: none
- Success response example: same shape as a single statistic object above.

#### PUT/PATCH `/api/v1/admin/statistics/{statistic}`

- Auth: Bearer token required
- Content-Type: `application/json`
- Payload example:

```json
{
    "label": "Projects Delivered",
    "value": 150,
    "unit": "+"
}
```

- Success response example: same shape as a single statistic object above, with updated values.

#### DELETE `/api/v1/admin/statistics/{statistic}`

- Auth: Bearer token required
- Payload example: none
- Success response example:

```json
{
    "status": 1,
    "message": "Statistique supprimée avec succès."
}
```

### Testimonials

#### GET `/api/v1/admin/testimonials`

- Auth: Bearer token required
- Payload example: none
- Success response example:

```json
{
    "status": 1,
    "message": "Liste des témoignages chargée avec succès.",
    "data": [
        {
            "id": 5,
            "project_id": 9,
            "client_id": 2,
            "content": "Great collaboration from kickoff to delivery.",
            "created_at": "2026-06-23T11:46:25.000000Z",
            "updated_at": "2026-06-23T11:46:25.000000Z",
            "client": {
                "id": 2,
                "first_name": "Sonia",
                "last_name": "Mballa",
                "job_title": "CEO"
            },
            "project": {
                "id": 9,
                "title": "Corporate Portal",
                "short_description": "Private portal for internal staff."
            },
            "created_by": {
                "id": 1,
                "name": "Admin User",
                "email": "admin@example.com"
            },
            "updated_by": null
        }
    ]
}
```

#### POST `/api/v1/admin/testimonials`

- Auth: Bearer token required
- Content-Type: `application/json`
- Payload example:

```json
{
    "project_id": 9,
    "client_id": 2,
    "content": "Great collaboration from kickoff to delivery."
}
```

- Success response example: same shape as a single testimonial object above.

#### GET `/api/v1/admin/testimonials/{testimonial}`

- Auth: Bearer token required
- Payload example: none
- Success response example: same shape as a single testimonial object above.

#### PUT/PATCH `/api/v1/admin/testimonials/{testimonial}`

- Auth: Bearer token required
- Content-Type: `application/json`
- Payload example:

```json
{
    "project_id": 9,
    "client_id": 2,
    "content": "Updated testimonial content."
}
```

- Success response example: same shape as a single testimonial object above, with updated values.

#### DELETE `/api/v1/admin/testimonials/{testimonial}`

- Auth: Bearer token required
- Payload example: none
- Success response example:

```json
{
    "status": 1,
    "message": "Témoignage supprimé avec succès."
}
```

### Visions

#### GET `/api/v1/admin/visions`

- Auth: Bearer token required
- Payload example: none
- Success response example:

```json
{
    "status": 1,
    "message": "Liste des visions chargée avec succès.",
    "data": [
        {
            "id": 1,
            "title": "Build sustainable digital products",
            "description": "We focus on maintainable systems and measurable business value.",
            "author": "SPA TECH",
            "created_at": "2026-06-23T11:46:04.000000Z",
            "updated_at": "2026-06-23T11:46:04.000000Z",
            "created_by": {
                "id": 1,
                "name": "Admin User",
                "email": "admin@example.com"
            },
            "updated_by": null
        }
    ]
}
```

#### POST `/api/v1/admin/visions`

- Auth: Bearer token required
- Content-Type: `application/json`
- Payload example:

```json
{
    "title": "Build sustainable digital products",
    "description": "We focus on maintainable systems and measurable business value.",
    "author": "SPA TECH"
}
```

- Success response example: same shape as a single vision object above.

#### GET `/api/v1/admin/visions/{vision}`

- Auth: Bearer token required
- Payload example: none
- Success response example: same shape as a single vision object above.

#### PUT/PATCH `/api/v1/admin/visions/{vision}`

- Auth: Bearer token required
- Content-Type: `application/json`
- Payload example:

```json
{
    "title": "Build maintainable platforms",
    "description": "Updated vision statement.",
    "author": "SPA TECH"
}
```

- Success response example: same shape as a single vision object above, with updated values.

#### DELETE `/api/v1/admin/visions/{vision}`

- Auth: Bearer token required
- Payload example: none
- Success response example:

```json
{
    "status": 1,
    "message": "Vision supprimée avec succès."
}
```

---

## Jobs Public

### GET `/api/jobs/developer-moments`

- Auth: public
- Payload example: none
- Success response example:

```json
{
    "status": 1,
    "message": "Developer moments fetched successfully.",
    "data": [
        {
            "id": 1,
            "name": "Jane Doe",
            "photo": "developer-moments/jane.jpg",
            "position": "Frontend Engineer",
            "quote": "Clean APIs make fast frontends.",
            "description": "Angular specialist with API integration experience.",
            "is_active": true,
            "created_at": "2026-06-30 12:00:00"
        }
    ]
}
```

### GET `/api/jobs/developer-moments/{developerMoment}`

- Auth: public
- Payload example: none
- Success response example: same shape as a single developer moment object above.

### GET `/api/jobs/pages`

- Auth: public
- Payload example: none
- Success response example:

```json
{
    "status": 1,
    "message": "Pages fetched successfully.",
    "data": [
        {
            "id": 1,
            "label": "Careers",
            "link": "/careers",
            "description": "Main careers page.",
            "is_active": true,
            "heroes": [],
            "created_at": "2026-06-30 12:00:00"
        }
    ]
}
```

### GET `/api/jobs/pages/{page}`

- Auth: public
- Payload example: none
- Success response example: same shape as a single page object above.

### GET `/api/jobs/heroes`

- Auth: public
- Payload example: none
- Success response example:

```json
{
    "status": 1,
    "message": "Heroes fetched successfully.",
    "data": [
        {
            "id": 1,
            "page_id": 1,
            "title": "Join our team",
            "sub_description": "Build products that matter.",
            "file": "heroes/careers-banner.jpg",
            "is_active": true,
            "page": {
                "id": 1,
                "label": "Careers",
                "link": "/careers",
                "description": "Main careers page.",
                "is_active": true,
                "created_at": "2026-06-30 12:00:00"
            },
            "created_at": "2026-06-30 12:05:00"
        }
    ]
}
```

### GET `/api/jobs/heroes/{hero}`

- Auth: public
- Payload example: none
- Success response example: same shape as a single hero object above.

### GET `/api/jobs/openings`

- Auth: public
- Payload example: none
- Success response example:

```json
{
    "status": 1,
    "message": "Openings fetched successfully.",
    "data": [
        {
            "id": 4,
            "title": "Senior Angular Developer",
            "short_description": "Lead frontend architecture and delivery.",
            "description": "Full job description...",
            "skills": ["Angular", "TypeScript", "REST APIs"],
            "image": "openings/angular-senior.jpg",
            "closing_date": "2026-07-31",
            "is_active": true,
            "applications": [],
            "created_at": "2026-06-30 12:10:00"
        }
    ]
}
```

### GET `/api/jobs/openings/{jobOpening}`

- Auth: public
- Payload example: none
- Success response example: same shape as a single job opening object above.

### POST `/api/jobs/applications`

- Auth: public
- Content-Type: `multipart/form-data`
- Payload example:

```json
{
    "job_opening_id": 4,
    "last_name": "Doe",
    "first_name": "John",
    "email": "john.doe@example.com",
    "phone": "+237690000002",
    "cv_file": "<binary pdf/doc/docx file>",
    "drive_link": "https://drive.google.com/file/d/abc123/view"
}
```

- Success response example:

```json
{
    "status": 1,
    "message": "Application submitted successfully.",
    "data": {
        "id": 21,
        "job_opening_id": 4,
        "last_name": "Doe",
        "first_name": "John",
        "email": "john.doe@example.com",
        "phone": "+237690000002",
        "cv_file": "applications/john-doe-cv.pdf",
        "drive_link": "https://drive.google.com/file/d/abc123/view",
        "status": false,
        "application_status": "pending",
        "job_opening": null,
        "processes": [],
        "created_at": "2026-06-30 12:20:00"
    }
}
```

### POST `/api/jobs/newsletters`

- Auth: public
- Content-Type: `application/json`
- Payload example:

```json
{
    "name": "John Doe",
    "phone": "+237690000002",
    "email": "john.doe@example.com"
}
```

- Success response example:

```json
{
    "status": 1,
    "message": "Newsletter subscription created successfully.",
    "data": {
        "id": 8,
        "name": "John Doe",
        "phone": "+237690000002",
        "email": "john.doe@example.com",
        "is_subscribed": true,
        "created_at": "2026-06-30 12:25:00"
    }
}
```

### POST `/api/jobs/quotes`

- Auth: public
- Content-Type: `application/json`
- Payload example:

```json
{
    "project_name": "New SaaS Platform",
    "description": "We need discovery, design and development.",
    "estimated_budget": "5000000",
    "expected_deadline": "2026-09-30",
    "full_name": "John Doe",
    "email": "john.doe@example.com",
    "phone": "+237690000002",
    "company": "ACME Ltd"
}
```

- Success response example:

```json
{
    "status": 1,
    "message": "Quote request created successfully.",
    "data": {
        "id": 10,
        "project_name": "New SaaS Platform",
        "description": "We need discovery, design and development.",
        "estimated_budget": "5000000",
        "expected_deadline": "2026-09-30",
        "full_name": "John Doe",
        "email": "john.doe@example.com",
        "phone": "+237690000002",
        "company": "ACME Ltd",
        "status": "pending",
        "is_approved": false,
        "created_at": "2026-06-30 12:30:00"
    }
}
```

---

## Jobs Admin

### Developer Moments

#### GET `/api/admin/jobs/developer-moments`

- Auth: Bearer token required
- Payload example: none
- Success response example: same shape as public developer moments list.

#### POST `/api/admin/jobs/developer-moments`

- Auth: Bearer token required
- Content-Type: `multipart/form-data`
- Payload example:

```json
{
    "name": "Jane Doe",
    "photo": "<binary image file>",
    "position": "Frontend Engineer",
    "quote": "Clean APIs make fast frontends.",
    "description": "Angular specialist with API integration experience.",
    "is_active": true
}
```

- Success response example: same shape as a single developer moment object above.

#### GET `/api/admin/jobs/developer-moments/{developer_moment}`

- Auth: Bearer token required
- Payload example: none
- Success response example: same shape as a single developer moment object above.

#### PUT/PATCH `/api/admin/jobs/developer-moments/{developer_moment}`

- Auth: Bearer token required
- Content-Type: `multipart/form-data`
- Payload example:

```json
{
    "name": "Jane Doe",
    "photo": "<binary image file>",
    "position": "Senior Frontend Engineer",
    "quote": "Updated quote.",
    "description": "Updated profile.",
    "is_active": true
}
```

- Success response example: same shape as a single developer moment object above, with updated values.

#### DELETE `/api/admin/jobs/developer-moments/{developer_moment}`

- Auth: Bearer token required
- Payload example: none
- Success response example:

```json
{
    "status": 1,
    "message": "Developer moment deleted successfully."
}
```

#### PATCH `/api/admin/jobs/developer-moments/{developerMoment}/switch-status`

- Auth: Bearer token required
- Payload example: none
- Success response example:

```json
{
    "status": 1,
    "message": "Developer moment status switched successfully."
}
```

### Pages

#### GET `/api/admin/jobs/pages`

- Auth: Bearer token required
- Payload example: none
- Success response example: same shape as public pages list.

#### POST `/api/admin/jobs/pages`

- Auth: Bearer token required
- Content-Type: `application/json`
- Payload example:

```json
{
    "label": "Careers",
    "link": "/careers",
    "description": "Main careers page.",
    "is_active": true
}
```

- Success response example: same shape as a single page object above.

#### GET `/api/admin/jobs/pages/{page}`

- Auth: Bearer token required
- Payload example: none
- Success response example: same shape as a single page object above.

#### PUT/PATCH `/api/admin/jobs/pages/{page}`

- Auth: Bearer token required
- Content-Type: `application/json`
- Payload example:

```json
{
    "label": "Careers",
    "link": "/careers",
    "description": "Updated page description.",
    "is_active": true
}
```

- Success response example: same shape as a single page object above, with updated values.

#### DELETE `/api/admin/jobs/pages/{page}`

- Auth: Bearer token required
- Payload example: none
- Success response example:

```json
{
    "status": 1,
    "message": "Page deleted successfully."
}
```

#### PATCH `/api/admin/jobs/pages/{page}/switch-status`

- Auth: Bearer token required
- Payload example: none
- Success response example:

```json
{
    "status": 1,
    "message": "Page status switched successfully."
}
```

### Heroes

#### GET `/api/admin/jobs/heroes`

- Auth: Bearer token required
- Payload example: none
- Success response example: same shape as public heroes list.

#### POST `/api/admin/jobs/heroes`

- Auth: Bearer token required
- Content-Type: `multipart/form-data`
- Payload example:

```json
{
    "page_id": 1,
    "title": "Join our team",
    "sub_description": "Build products that matter.",
    "file": "<binary image/file>",
    "is_active": true
}
```

- Success response example: same shape as a single hero object above.

#### GET `/api/admin/jobs/heroes/{hero}`

- Auth: Bearer token required
- Payload example: none
- Success response example: same shape as a single hero object above.

#### PUT/PATCH `/api/admin/jobs/heroes/{hero}`

- Auth: Bearer token required
- Content-Type: `multipart/form-data`
- Payload example:

```json
{
    "page_id": 1,
    "title": "Join our product team",
    "sub_description": "Updated hero subtitle.",
    "file": "<binary image/file>",
    "is_active": true
}
```

- Success response example: same shape as a single hero object above, with updated values.

#### DELETE `/api/admin/jobs/heroes/{hero}`

- Auth: Bearer token required
- Payload example: none
- Success response example:

```json
{
    "status": 1,
    "message": "Hero deleted successfully."
}
```

#### PATCH `/api/admin/jobs/heroes/{hero}/switch-status`

- Auth: Bearer token required
- Payload example: none
- Success response example:

```json
{
    "status": 1,
    "message": "Hero status switched successfully."
}
```

### Openings

#### GET `/api/admin/jobs/openings`

- Auth: Bearer token required
- Payload example: none
- Success response example: same shape as public openings list.

#### POST `/api/admin/jobs/openings`

- Auth: Bearer token required
- Content-Type: `multipart/form-data`
- Payload example:

```json
{
    "title": "Senior Angular Developer",
    "short_description": "Lead frontend architecture and delivery.",
    "description": "Full job description...",
    "skills": ["Angular", "TypeScript", "REST APIs"],
    "image": "<binary image file>",
    "closing_date": "2026-07-31",
    "is_active": true
}
```

- Success response example: same shape as a single job opening object above.

#### GET `/api/admin/jobs/openings/{opening}`

- Auth: Bearer token required
- Payload example: none
- Success response example: same shape as a single job opening object above.

#### PUT/PATCH `/api/admin/jobs/openings/{opening}`

- Auth: Bearer token required
- Content-Type: `multipart/form-data`
- Payload example:

```json
{
    "title": "Senior Angular Engineer",
    "short_description": "Updated opening summary.",
    "description": "Updated job description...",
    "skills": ["Angular", "TypeScript", "REST APIs", "RxJS"],
    "image": "<binary image file>",
    "closing_date": "2026-08-15",
    "is_active": true
}
```

- Success response example: same shape as a single job opening object above, with updated values.

#### DELETE `/api/admin/jobs/openings/{opening}`

- Auth: Bearer token required
- Payload example: none
- Success response example:

```json
{
    "status": 1,
    "message": "Opening deleted successfully."
}
```

#### PATCH `/api/admin/jobs/openings/{opening}/switch-status`

- Auth: Bearer token required
- Payload example: none
- Success response example:

```json
{
    "status": 1,
    "message": "Opening status switched successfully."
}
```

### Applications

#### GET `/api/admin/jobs/applications`

- Auth: Bearer token required
- Payload example: none
- Success response example:

```json
{
    "status": 1,
    "message": "Applications fetched successfully.",
    "data": [
        {
            "id": 21,
            "job_opening_id": 4,
            "last_name": "Doe",
            "first_name": "John",
            "email": "john.doe@example.com",
            "phone": "+237690000002",
            "cv_file": "applications/john-doe-cv.pdf",
            "drive_link": "https://drive.google.com/file/d/abc123/view",
            "status": false,
            "application_status": "pending",
            "job_opening": {
                "id": 4,
                "title": "Senior Angular Developer",
                "short_description": "Lead frontend architecture and delivery.",
                "description": "Full job description...",
                "skills": ["Angular", "TypeScript", "REST APIs"],
                "image": "openings/angular-senior.jpg",
                "closing_date": "2026-07-31",
                "is_active": true,
                "created_at": "2026-06-30 12:10:00"
            },
            "processes": [
                {
                    "id": 3,
                    "job_application_id": 21,
                    "title": "CV Review",
                    "description": "Initial recruiter review.",
                    "status": "completed",
                    "is_completed": true,
                    "processed_by": 1,
                    "processed_at": "2026-06-30 13:00:00",
                    "processor": {
                        "id": 1,
                        "name": "Admin User",
                        "email": "admin@example.com"
                    },
                    "created_at": "2026-06-30 12:50:00"
                }
            ],
            "created_at": "2026-06-30 12:20:00"
        }
    ]
}
```

#### GET `/api/admin/jobs/applications/{application}`

- Auth: Bearer token required
- Payload example: none
- Success response example: same shape as a single application object above.

#### PUT/PATCH `/api/admin/jobs/applications/{application}`

- Auth: Bearer token required
- Content-Type: `multipart/form-data`
- Payload example:

```json
{
    "job_opening_id": 4,
    "last_name": "Doe",
    "first_name": "John",
    "email": "john.doe@example.com",
    "phone": "+237690000002",
    "cv_file": "<binary pdf/doc/docx file>",
    "drive_link": "https://drive.google.com/file/d/abc123/view",
    "status": "accepted"
}
```

- Success response example: same shape as a single application object above, where:
    - `application_status` becomes `"accepted"`
    - boolean `status` becomes `true`

#### DELETE `/api/admin/jobs/applications/{application}`

- Auth: Bearer token required
- Payload example: none
- Success response example:

```json
{
    "status": 1,
    "message": "Application deleted successfully."
}
```

### Application Processes

#### GET `/api/admin/jobs/application-processes`

- Auth: Bearer token required
- Payload example: none
- Success response example:

```json
{
    "status": 1,
    "message": "Application processes fetched successfully.",
    "data": [
        {
            "id": 3,
            "job_application_id": 21,
            "title": "CV Review",
            "description": "Initial recruiter review.",
            "status": "completed",
            "is_completed": true,
            "processed_by": 1,
            "processed_at": "2026-06-30 13:00:00",
            "processor": {
                "id": 1,
                "name": "Admin User",
                "email": "admin@example.com"
            },
            "created_at": "2026-06-30 12:50:00"
        }
    ]
}
```

#### POST `/api/admin/jobs/application-processes`

- Auth: Bearer token required
- Content-Type: `application/json`
- Payload example:

```json
{
    "job_application_id": 21,
    "title": "CV Review",
    "description": "Initial recruiter review.",
    "status": "completed",
    "processed_by": 1,
    "processed_at": "2026-06-30 13:00:00"
}
```

- Success response example: same shape as a single application process object above.

#### GET `/api/admin/jobs/application-processes/{application_process}`

- Auth: Bearer token required
- Payload example: none
- Success response example: same shape as a single application process object above.

#### PUT/PATCH `/api/admin/jobs/application-processes/{application_process}`

- Auth: Bearer token required
- Content-Type: `application/json`
- Payload example:

```json
{
    "job_application_id": 21,
    "title": "Technical Interview",
    "description": "Second step in the process.",
    "status": "pending",
    "processed_by": 1,
    "processed_at": "2026-07-02 10:00:00"
}
```

- Success response example: same shape as a single application process object above, with updated values.

#### DELETE `/api/admin/jobs/application-processes/{application_process}`

- Auth: Bearer token required
- Payload example: none
- Success response example:

```json
{
    "status": 1,
    "message": "Application process deleted successfully."
}
```

### Newsletters

#### GET `/api/admin/jobs/newsletters`

- Auth: Bearer token required
- Payload example: none
- Success response example:

```json
{
    "status": 1,
    "message": "Newsletters fetched successfully.",
    "data": [
        {
            "id": 8,
            "name": "John Doe",
            "phone": "+237690000002",
            "email": "john.doe@example.com",
            "is_subscribed": true,
            "created_at": "2026-06-30 12:25:00"
        }
    ]
}
```

#### GET `/api/admin/jobs/newsletters/{newsletter}`

- Auth: Bearer token required
- Payload example: none
- Success response example: same shape as a single newsletter object above.

#### DELETE `/api/admin/jobs/newsletters/{newsletter}`

- Auth: Bearer token required
- Payload example: none
- Success response example:

```json
{
    "status": 1,
    "message": "Newsletter deleted successfully."
}
```

#### PATCH `/api/admin/jobs/newsletters/{newsletter}/switch-status`

- Auth: Bearer token required
- Payload example: none
- Success response example:

```json
{
    "status": 1,
    "message": "Newsletter status switched successfully."
}
```

### Quotes

#### GET `/api/admin/jobs/quotes`

- Auth: Bearer token required
- Payload example: none
- Success response example:

```json
{
    "status": 1,
    "message": "Quotes fetched successfully.",
    "data": [
        {
            "id": 10,
            "project_name": "New SaaS Platform",
            "description": "We need discovery, design and development.",
            "estimated_budget": "5000000",
            "expected_deadline": "2026-09-30",
            "full_name": "John Doe",
            "email": "john.doe@example.com",
            "phone": "+237690000002",
            "company": "ACME Ltd",
            "status": "pending",
            "is_approved": false,
            "created_at": "2026-06-30 12:30:00"
        }
    ]
}
```

#### GET `/api/admin/jobs/quotes/{quote}`

- Auth: Bearer token required
- Payload example: none
- Success response example: same shape as a single quote object above.

#### PUT/PATCH `/api/admin/jobs/quotes/{quote}`

- Auth: Bearer token required
- Content-Type: `application/json`
- Payload example:

```json
{
    "project_name": "New SaaS Platform",
    "description": "Updated quote request details.",
    "estimated_budget": "6000000",
    "expected_deadline": "2026-10-15",
    "full_name": "John Doe",
    "email": "john.doe@example.com",
    "phone": "+237690000002",
    "company": "ACME Ltd",
    "status": "approved"
}
```

- Success response example: same shape as a single quote object above, where `is_approved` becomes `true`.

#### DELETE `/api/admin/jobs/quotes/{quote}`

- Auth: Bearer token required
- Payload example: none
- Success response example:

```json
{
    "status": 1,
    "message": "Quote deleted successfully."
}
```

---

## Route Inventory

This document covers the following currently registered API routes:

- `GET /api/user`
- `POST /api/v1/auth/register`
- `POST /api/v1/auth/login`
- `POST /api/v1/auth/logout`
- `GET /api/v1/articles`
- `GET /api/v1/articles/{id}`
- `GET /api/v1/articles/{article}/comments`
- `POST /api/v1/articles/{article}/comments`
- `POST /api/v1/admin/articles`
- `PUT /api/v1/admin/articles/{id}`
- `DELETE /api/v1/admin/articles/{id}`
- `DELETE /api/v1/admin/comments/{id}`
- `POST /api/v1/contacts`
- `GET /api/v1/admin/contacts`
- `GET /api/v1/admin/contacts/{id}`
- `DELETE /api/v1/admin/contacts/{id}`
- `GET /api/v1/offers`
- `GET /api/v1/offers/{id}`
- `GET /api/v1/admin/offer-types`
- `POST /api/v1/admin/offer-types`
- `GET /api/v1/admin/offer-types/{offer_type}`
- `PUT|PATCH /api/v1/admin/offer-types/{offer_type}`
- `DELETE /api/v1/admin/offer-types/{offer_type}`
- `POST /api/v1/admin/offers`
- `PUT /api/v1/admin/offers/{id}`
- `DELETE /api/v1/admin/offers/{id}`
- `GET /api/v1/admin/categories/{id}/category`
- `GET /api/v1/admin/categories`
- `POST /api/v1/admin/categories`
- `GET /api/v1/admin/categories/{category}`
- `PUT|PATCH /api/v1/admin/categories/{category}`
- `DELETE /api/v1/admin/categories/{category}`
- `GET /api/v1/admin/tags/{id}/tag`
- `GET /api/v1/admin/tags`
- `POST /api/v1/admin/tags`
- `GET /api/v1/admin/tags/{tag}`
- `PUT|PATCH /api/v1/admin/tags/{tag}`
- `DELETE /api/v1/admin/tags/{tag}`
- `GET /api/v1/projects`
- `GET /api/v1/projects/{id}`
- `GET /api/v1/testimonials`
- `GET /api/v1/visions`
- `GET /api/v1/admin/clients`
- `POST /api/v1/admin/clients`
- `GET /api/v1/admin/clients/{client}`
- `PUT|PATCH /api/v1/admin/clients/{client}`
- `DELETE /api/v1/admin/clients/{client}`
- `GET /api/v1/admin/partners`
- `POST /api/v1/admin/partners`
- `GET /api/v1/admin/partners/{partner}`
- `PUT|PATCH /api/v1/admin/partners/{partner}`
- `DELETE /api/v1/admin/partners/{partner}`
- `GET /api/v1/admin/projects`
- `POST /api/v1/admin/projects`
- `GET /api/v1/admin/projects/{project}`
- `PUT|PATCH /api/v1/admin/projects/{project}`
- `DELETE /api/v1/admin/projects/{project}`
- `GET /api/v1/admin/services`
- `POST /api/v1/admin/services`
- `GET /api/v1/admin/services/{service}`
- `PUT|PATCH /api/v1/admin/services/{service}`
- `DELETE /api/v1/admin/services/{service}`
- `GET /api/v1/admin/statistics`
- `POST /api/v1/admin/statistics`
- `GET /api/v1/admin/statistics/{statistic}`
- `PUT|PATCH /api/v1/admin/statistics/{statistic}`
- `DELETE /api/v1/admin/statistics/{statistic}`
- `GET /api/v1/admin/testimonials`
- `POST /api/v1/admin/testimonials`
- `GET /api/v1/admin/testimonials/{testimonial}`
- `PUT|PATCH /api/v1/admin/testimonials/{testimonial}`
- `DELETE /api/v1/admin/testimonials/{testimonial}`
- `GET /api/v1/admin/visions`
- `POST /api/v1/admin/visions`
- `GET /api/v1/admin/visions/{vision}`
- `PUT|PATCH /api/v1/admin/visions/{vision}`
- `DELETE /api/v1/admin/visions/{vision}`
- `GET /api/jobs/developer-moments`
- `GET /api/jobs/developer-moments/{developerMoment}`
- `GET /api/jobs/pages`
- `GET /api/jobs/pages/{page}`
- `GET /api/jobs/heroes`
- `GET /api/jobs/heroes/{hero}`
- `GET /api/jobs/openings`
- `GET /api/jobs/openings/{jobOpening}`
- `POST /api/jobs/applications`
- `POST /api/jobs/newsletters`
- `POST /api/jobs/quotes`
- `GET /api/admin/jobs/developer-moments`
- `POST /api/admin/jobs/developer-moments`
- `GET /api/admin/jobs/developer-moments/{developer_moment}`
- `PUT|PATCH /api/admin/jobs/developer-moments/{developer_moment}`
- `DELETE /api/admin/jobs/developer-moments/{developer_moment}`
- `PATCH /api/admin/jobs/developer-moments/{developerMoment}/switch-status`
- `GET /api/admin/jobs/pages`
- `POST /api/admin/jobs/pages`
- `GET /api/admin/jobs/pages/{page}`
- `PUT|PATCH /api/admin/jobs/pages/{page}`
- `DELETE /api/admin/jobs/pages/{page}`
- `PATCH /api/admin/jobs/pages/{page}/switch-status`
- `GET /api/admin/jobs/heroes`
- `POST /api/admin/jobs/heroes`
- `GET /api/admin/jobs/heroes/{hero}`
- `PUT|PATCH /api/admin/jobs/heroes/{hero}`
- `DELETE /api/admin/jobs/heroes/{hero}`
- `PATCH /api/admin/jobs/heroes/{hero}/switch-status`
- `GET /api/admin/jobs/openings`
- `POST /api/admin/jobs/openings`
- `GET /api/admin/jobs/openings/{opening}`
- `PUT|PATCH /api/admin/jobs/openings/{opening}`
- `DELETE /api/admin/jobs/openings/{opening}`
- `PATCH /api/admin/jobs/openings/{opening}/switch-status`
- `GET /api/admin/jobs/applications`
- `GET /api/admin/jobs/applications/{application}`
- `PUT|PATCH /api/admin/jobs/applications/{application}`
- `DELETE /api/admin/jobs/applications/{application}`
- `GET /api/admin/jobs/application-processes`
- `POST /api/admin/jobs/application-processes`
- `GET /api/admin/jobs/application-processes/{application_process}`
- `PUT|PATCH /api/admin/jobs/application-processes/{application_process}`
- `DELETE /api/admin/jobs/application-processes/{application_process}`
- `GET /api/admin/jobs/newsletters`
- `GET /api/admin/jobs/newsletters/{newsletter}`
- `DELETE /api/admin/jobs/newsletters/{newsletter}`
- `PATCH /api/admin/jobs/newsletters/{newsletter}/switch-status`
- `GET /api/admin/jobs/quotes`
- `GET /api/admin/jobs/quotes/{quote}`
- `PUT|PATCH /api/admin/jobs/quotes/{quote}`
- `DELETE /api/admin/jobs/quotes/{quote}`
