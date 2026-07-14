# Formation Module Documentation

## Overview

This document describes the Formation (Training) module, which manages training categories, training sessions, content images, participants, participations, and payments. Also includes documentation for the selected public partner endpoints from the Website module.

## Shared Response Envelopes

All endpoints use the same standard response envelopes as defined in `ApiResponses` trait:

### Success Response

```json
{
  "status": 1,
  "message": "...",
  "data": ...
}
```

### No-Content Success

```json
{
    "status": 1,
    "message": "..."
}
```

### Error Response

```json
{
  "status": 0,
  "message": "...",
  "error": ...
}
```

---

## Formation Categories

### Public Endpoints

#### Get All Formation Categories

- **Route**: `GET /api/v1/formation-categories`
- **Auth**: No
- **Description**: List all training categories ordered by label
- **Response**:

```json
{
    "status": 1,
    "message": "Liste des catégories de formation chargée avec succès.",
    "data": [
        {
            "id": 1,
            "libelle": "Développement Web",
            "is_active": true
        }
    ]
}
```

#### Get Single Formation Category

- **Route**: `GET /api/v1/formation-categories/{formationCategory}`
- **Auth**: No
- **Parameters**:
    - `formationCategory`: Category ID (number)
- **Response**:

```json
{
    "status": 1,
    "message": "Catégorie de formation chargée avec succès.",
    "data": {
        "id": 1,
        "libelle": "Développement Web",
        "is_active": true
    }
}
```

### Admin Endpoints (Protected by `auth:sanctum`)

#### Create Formation Category

- **Route**: `POST /api/v1/admin/formation-categories`
- **Auth**: Yes (`auth:sanctum`)
- **Payload**:

```json
{
    "libelle": "Développement Mobile",
    "is_active": true
}
```

- **Response**:

```json
{
    "status": 1,
    "message": "Catégorie de formation créée avec succès.",
    "data": {
        "id": 2,
        "libelle": "Développement Mobile",
        "is_active": true,
        "created_by": "John Doe",
        "updated_by": "John Doe"
    }
}
```

#### Update Formation Category

- **Route**: `PUT|PATCH /api/v1/admin/formation-categories/{formationCategory}`
- **Auth**: Yes (`auth:sanctum`)
- **Parameters**:
    - `formationCategory`: Category ID (number)
- **Payload**:

```json
{
    "libelle": "Développement Mobile (iOS & Android)",
    "is_active": true
}
```

#### Delete Formation Category

- **Route**: `DELETE /api/v1/admin/formation-categories/{formationCategory}`
- **Auth**: Yes (`auth:sanctum`)
- **Description**: Soft deletes a category
- **Response**: No-content success

---

## Formations

### Formation Status Enum

Possible values:

- `en_attente`: Pending (waiting to start)
- `en_cours`: In progress
- `terminee`: Finished
- `annulee`: Canceled

### Public Endpoints

#### List Formations

- **Route**: `GET /api/v1/formations`
- **Auth**: No
- **Description**: List all formations, with optional filters
- **Filters (Query Params)**:
    - `category_id`: Filter by category ID
    - `status`: Filter by status (one of the enum values)
    - `is_active`: Filter by active state (boolean)
- **Response**:

```json
{
    "status": 1,
    "message": "Liste des formations chargée avec succès.",
    "data": [
        {
            "id": 1,
            "formation_category_id": 1,
            "libelle": "Formation Laravel 11",
            "short_description": "Apprenez Laravel 11 en 2 semaines",
            "description": "<p>Formation complète sur Laravel 11...</p>",
            "date_debut": "2024-07-01",
            "date_fin": "2024-07-15",
            "date_fin_inscription": "2024-06-25",
            "nombre_places": 20,
            "lieu_formation": "Tunis",
            "frais_inscription": 50.0,
            "frais_formation": 500.0,
            "status": "en_attente",
            "is_active": true,
            "thumbnail_url": "https://...",
            "category": {
                "id": 1,
                "libelle": "Développement Web"
            }
        }
    ]
}
```

#### Get Single Formation

- **Route**: `GET /api/v1/formations/{formation}`
- **Auth**: No
- **Parameters**:
    - `formation`: Formation ID (number)
- **Response**: Same structure as list, single item

### Admin Endpoints (Protected by `auth:sanctum`)

#### Create Formation

- **Route**: `POST /api/v1/admin/formations`
- **Auth**: Yes (`auth:sanctum`)
- **Payload (multipart/form-data)**:
    - `formation_category_id`: Required, integer, exists in formation_categories
    - `libelle`: Required, string (2-200 chars)
    - `short_description`: Optional, string
    - `description`: Required, string (min 2 chars)
    - `date_debut`: Required, date
    - `date_fin`: Required, date (after or equal to date_debut)
    - `nombre_places`: Required, integer (min 1)
    - `lieu_formation`: Required, string (2-255 chars)
    - `date_fin_inscription`: Required, date
    - `frais_inscription`: Required, numeric (0-9999999999999.99)
    - `frais_formation`: Required, numeric (0-9999999999999.99)
    - `thumbnail`: Optional, image (png/jpg/jpeg/webp, max 2MB)
- **Response**:

```json
{
    "status": 1,
    "message": "Formation créée avec succès.",
    "data": {
        "id": 2,
        "libelle": "Formation Laravel 11",
        "...": "..."
    }
}
```

#### Update Formation

- **Route**: `PUT|PATCH /api/v1/admin/formations/{formation}`
- **Auth**: Yes (`auth:sanctum`)
- **Parameters**:
    - `formation`: Formation ID (number)
- **Payload**: Same as create, all fields optional
- **Description**: Automatically syncs content images embedded in description HTML

#### Switch Formation Status

- **Route**: `PATCH /api/v1/admin/formations/{formation}/switch-status`
- **Auth**: Yes (`auth:sanctum`)
- **Parameters**:
    - `formation`: Formation ID (number)
- **Payload**:

```json
{
    "status": "en_cours"
}
```

- **Response**: Updated formation resource

#### Toggle Formation Active State

- **Route**: `PATCH /api/v1/admin/formations/{formation}/switch-state`
- **Auth**: Yes (`auth:sanctum`)
- **Parameters**:
    - `formation`: Formation ID (number)
- **Description**: Toggles `is_active` flag on/off
- **Response**: Updated formation resource

#### Delete Formation

- **Route**: `DELETE /api/v1/admin/formations/{formation}`
- **Auth**: Yes (`auth:sanctum`)
- **Description**: Soft deletes a formation
- **Response**: No-content success

---

## Formation Images (Description)

### Overview

This handles images embedded in the formation description field for CKEditor/Quill/Summernote style editors.

### Public Endpoints

#### Get Formation Image (by filename)

- **Route**: `GET /api/v1/formation-images/{image}`
- **Auth**: No
- **Parameters**:
    - `image`: Image filename (pattern: alphanumeric + hyphens + dot + extension)
- **Response**:

```json
{
    "status": 1,
    "message": "Image chargée avec succès.",
    "data": {
        "id": 1,
        "path": "https://..."
    }
}
```

### Admin Endpoints (Protected by `auth:sanctum`)

#### Upload Formation Content Image

- **Route**: `POST /api/v1/admin/formations/content-images`
- **Auth**: Yes (`auth:sanctum`)
- **Payload (multipart/form-data)**:
    - `upload`: Required, image file (png/jpg/jpeg/webp/gif, max 2MB)
- **Response**:

```json
{
    "status": 1,
    "message": "Image uploadée avec succès",
    "data": {
        "id": 2,
        "path": "https://..."
    }
}
```

- **Frontend Usage**:
    1. When user uploads an image in the rich text editor, call this endpoint first
    2. Use the returned `path` as the `<img src="...">` value
    3. Keep the returned `id` to delete later if needed
    4. When saving the formation, include the image path in description HTML as `/formation-images/{filename}`

#### Delete Formation Image

- **Route**: `DELETE /api/v1/admin/formation-images/{image}`
- **Auth**: Yes (`auth:sanctum`)
- **Parameters**:
    - `image`: FormationImage ID
- **Response**: No-content success

---

## Participants

### Admin Endpoints (Protected by `auth:sanctum`)

#### List Participants

- **Route**: `GET /api/v1/admin/participants`
- **Auth**: Yes (`auth:sanctum`)
- **Filters**:
    - `trashed=with`: Include soft-deleted participants
    - `trashed=only`: Show only soft-deleted participants

#### Get Single Participant

- **Route**: `GET /api/v1/admin/participants/{participant}`
- **Auth**: Yes (`auth:sanctum`)
- **Parameters**:
    - `participant`: Participant ID (number)

#### Update Participant

- **Route**: `PUT|PATCH /api/v1/admin/participants/{participant}`
- **Auth**: Yes (`auth:sanctum`)
- **Parameters**:
    - `participant`: Participant ID (number)
- **Payload**:
    - `nom`: String
    - `prenom`: String
    - `telephone`: String
    - `adresse`: Optional string
    - `avatar`: Optional image file

#### Delete Participant

- **Route**: `DELETE /api/v1/admin/participants/{participant}`
- **Auth**: Yes (`auth:sanctum`)
- **Description**: Soft deletes a participant

---

## Participations

### Public Endpoints

#### Register to a Formation

- **Route**: `POST /api/v1/formations/{formation}/participations`
- **Auth**: No
- **Parameters**:
    - `formation`: Formation ID (number)
- **Payload (multipart/form-data)**:
    - `nom`: Required, string
    - `prenom`: Required, string
    - `telephone`: Required, string
    - `adresse`: Optional, string
    - `avatar`: Optional, image file
- **Description**:
    - Creates a new participant if not exists (or restores soft-deleted)
    - Checks capacity, active state, and registration period of the formation
    - Restores previous participation if exists and soft-deleted
- **Response**:

```json
{
    "status": 1,
    "message": "Inscription enregistrée avec succès.",
    "data": {
        "id": 1,
        "formation_id": 1,
        "participant_id": 1
    }
}
```

### Admin Endpoints (Protected by `auth:sanctum`)

#### List Participations

- **Route**: `GET /api/v1/admin/participations`
- **Auth**: Yes (`auth:sanctum`)
- **Filters**:
    - `trashed`: Same as participants
    - `formation_id`: Filter by formation ID

#### Get Single Participation

- **Route**: `GET /api/v1/admin/participations/{participation}`
- **Auth**: Yes (`auth:sanctum`)
- **Parameters**:
    - `participation`: Participation ID (number)
- **Response**: Includes formation, participant, and payments

#### Switch Participation Status

- **Route**: `PATCH /api/v1/admin/participations/{participation}/switch-status`
- **Auth**: Yes (`auth:sanctum`)
- **Payload**:

```json
{
    "status": "confirmed"
}
```

#### Delete Participation

- **Route**: `DELETE /api/v1/admin/participations/{participation}`
- **Auth**: Yes (`auth:sanctum`)
- **Description**: Soft deletes a participation

---

## Payments

### Admin Endpoints (Protected by `auth:sanctum`)

#### List Payments for a Participation

- **Route**: `GET /api/v1/admin/participations/{participation}/payments`
- **Auth**: Yes (`auth:sanctum`)
- **Parameters**:
    - `participation`: Participation ID (number)

#### Record a Payment

- **Route**: `POST /api/v1/admin/participations/{participation}/payments`
- **Auth**: Yes (`auth:sanctum`)
- **Parameters**:
    - `participation`: Participation ID (number)
- **Payload**:
    - `montant`: Required, numeric
    - `date_paiement`: Required, date
    - `mode_paiement`: Required, string
    - `reference`: Optional, string
    - `notes`: Optional, string

#### Get Single Payment

- **Route**: `GET /api/v1/admin/payments/{payment}`
- **Auth**: Yes (`auth:sanctum`)

#### Update Payment

- **Route**: `PUT|PATCH /api/v1/admin/payments/{payment}`
- **Auth**: Yes (`auth:sanctum`)

#### Delete Payment

- **Route**: `DELETE /api/v1/admin/payments/{payment}`
- **Auth**: Yes (`auth:sanctum`)

---

## Website Module: Partner Public Endpoints (Added as Requested)

### Get All Active Partners

- **Route**: `GET /api/v1/partners`
- **Auth**: No
- **Description**: List all partners with `status = true`, ordered by creation date desc
- **Response**:

```json
{
    "status": 1,
    "message": "Liste des partenaires chargée avec succès.",
    "data": [
        {
            "id": 1,
            "name": "Partner Name",
            "acronym": "PN",
            "domain": "partner.com",
            "description": "Partner description",
            "logo_url": "https://..."
        }
    ]
}
```

### Get Single Active Partner

- **Route**: `GET /api/v1/partners/{id}`
- **Auth**: No
- **Parameters**:
    - `id`: Partner ID (number)
- **Response**: Single partner object, same structure as list
