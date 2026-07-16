# Sondage Module Documentation

## Overview
Ce module permet de gérer des sondages pronostics autour de compétitions sportives. Il inclut la gestion des compétitions, équipes, rencontres, sondages, votants et votes.

## Shared Response Envelopes
Tous les endpoints utilisent les mêmes enveloppes de réponse standard définies dans le trait `ApiResponses`:

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

## Competitions

### Public Endpoints

#### Get All Competitions
- **Route**: `GET /api/v1/competitions`
- **Auth**: No
- **Description**: Liste toutes les compétitions (seulement actives pour les visiteurs anonymes, toutes pour les admins authentifiés)
- **Response**:
```json
{
  "status": 1,
  "message": "Liste des compétitions chargée avec succès.",
  "data": [
    {
      "id": 1,
      "libelle": "Ligue 1",
      "description": "Championnat de France",
      "saison": "2024-2025",
      "is_active": true,
      "created_at": "2024-07-01 10:00:00",
      "updated_at": "2024-07-01 10:00:00"
    }
  ]
}
```

#### Get Single Competition
- **Route**: `GET /api/v1/competitions/{id}`
- **Auth**: No
- **Parameters**:
  - `id`: ID de la compétition (number)
- **Description**: Affiche le détail d'une compétition (seulement si active pour les visiteurs anonymes)

### Admin Endpoints (Protected by `auth:sanctum`)

#### Create Competition
- **Route**: `POST /api/v1/admin/competitions`
- **Auth**: Yes (`auth:sanctum`)
- **Payload**:
```json
{
  "libelle": "Ligue 1",
  "description": "Championnat de France",
  "saison": "2024-2025",
  "is_active": true
}
```

#### Update Competition
- **Route**: `PUT /api/v1/admin/competitions/{id}`
- **Auth**: Yes (`auth:sanctum`)
- **Parameters**:
  - `id`: ID de la compétition (number)
- **Payload**: Même que création (tous champs optionnels)

#### Toggle Competition Status
- **Route**: `GET /api/v1/admin/competitions/{id}/switch-status`
- **Auth**: Yes (`auth:sanctum`)
- **Description**: Active/désactive une compétition
- **Response**: No-content success

#### Delete Competition
- **Route**: `DELETE /api/v1/admin/competitions/{id}`
- **Auth**: Yes (`auth:sanctum`)
- **Description**: Supprime définitivement une compétition

---

## Competition Equipes

### Public Endpoints

#### Get Teams in Competition
- **Route**: `GET /api/v1/competitions/{competitionId}/equipes`
- **Auth**: No
- **Parameters**:
  - `competitionId`: ID de la compétition (number)
- **Description**: Liste toutes les équipes engagées dans une compétition

### Admin Endpoints (Protected by `auth:sanctum`)

#### Get All Competition-Team Links
- **Route**: `GET /api/v1/admin/competition-equipes`
- **Auth**: Yes (`auth:sanctum`)
- **Description**: Liste tous les liens entre compétitions et équipes

#### Add Team to Competition
- **Route**: `POST /api/v1/admin/competitions/{competitionId}/equipes`
- **Auth**: Yes (`auth:sanctum`)
- **Parameters**:
  - `competitionId`: ID de la compétition (number)
- **Payload**:
```json
{
  "equipe_id": 1
}
```

#### Remove Team from Competition
- **Route**: `DELETE /api/v1/admin/competitions/{competitionId}/equipes/{equipeId}`
- **Auth**: Yes (`auth:sanctum`)
- **Parameters**:
  - `competitionId`: ID de la compétition (number)
  - `equipeId`: ID de l'équipe (number)

---

## Equipes

### Public Endpoints

#### Get All Teams
- **Route**: `GET /api/v1/equipes`
- **Auth**: No
- **Description**: Liste toutes les équipes, triées par libellé
- **Response**:
```json
{
  "status": 1,
  "message": "Liste des équipes chargée avec succès.",
  "data": [
    {
      "id": 1,
      "libelle": "Équipe A",
      "description": "Description de l'équipe A"
    }
  ]
}
```

#### Get Single Team
- **Route**: `GET /api/v1/equipes/{id}`
- **Auth**: No
- **Parameters**:
  - `id`: ID de l'équipe (number)

### Admin Endpoints (Protected by `auth:sanctum`)

#### Create Team
- **Route**: `POST /api/v1/admin/equipes`
- **Auth**: Yes (`auth:sanctum`)
- **Payload**:
```json
{
  "libelle": "Équipe A",
  "description": "Description de l'équipe A"
}
```

#### Update Team
- **Route**: `PUT /api/v1/admin/equipes/{id}`
- **Auth**: Yes (`auth:sanctum`)
- **Parameters**:
  - `id`: ID de l'équipe (number)
- **Payload**: Même que création (tous champs optionnels)

#### Delete Team
- **Route**: `DELETE /api/v1/admin/equipes/{id}`
- **Auth**: Yes (`auth:sanctum`)
- **Description**: Supprime définitivement une équipe

---

## Sondages (InitSondage)

### Public Endpoints

#### Get All Polls
- **Route**: `GET /api/v1/sondages`
- **Auth**: No
- **Description**: Liste tous les sondages (seulement actifs pour les visiteurs anonymes)
- **Response**:
```json
{
  "status": 1,
  "message": "Liste des sondages chargée avec succès.",
  "data": [
    {
      "id": 1,
      "competition": {
        "id": 1,
        "libelle": "Ligue 1"
      },
      "libelle": "Pronostic de la saison 2024-2025",
      "description": "Description du sondage",
      "avantage": {
        "equipe1": "Bonus de 10%",
        "equipe2": "Bonus de 5%"
      },
      "heure_debut": "2024-07-01 10:00:00",
      "heure_fin": "2024-07-15 10:00:00",
      "niveau_vote": [
        "quarter_final",
        "semi_final",
        "final"
      ],
      "cadeaux": {
        "premier": ["250 000 GNF", "2 T-Shirts"],
        "deuxieme": ["100 000 GNF", "1 T-Shirt"]
      },
      "image": "https://cdn.example.com/sondages/123.jpg",
      "is_active": true,
      "created_at": "2024-07-01 10:00:00",
      "updated_at": "2024-07-01 10:00:00"
    }
  ]
}
```

#### Get Single Poll
- **Route**: `GET /api/v1/sondages/{id}`
- **Auth**: No
- **Parameters**:
  - `id`: ID du sondage (number)

### Admin Endpoints (Protected by `auth:sanctum`)

#### Create Poll
- **Route**: `POST /api/v1/admin/sondages`
- **Auth**: Yes (`auth:sanctum`)
- **Payload** (multipart/form-data):
  - `competition_id`: Required, number
  - `libelle`: Required, string
  - `description`: Required, string
  - `avantage`: Optional, array
  - `heure_debut`: Required, datetime
  - `heure_fin`: Required, datetime
  - `niveau_vote`: Required, array
  - `cadeaux`: Required, array
  - `image`: Optional, file (image)

#### Update Poll
- **Route**: `PUT /api/v1/admin/sondages/{id}`
- **Auth**: Yes (`auth:sanctum`)
- **Parameters**:
  - `id`: ID du sondage (number)
- **Payload**: Même que création (tous champs optionnels)
- **Description**: Si une nouvelle image est envoyée, l'ancienne est supprimée

#### Toggle Poll Status
- **Route**: `GET /api/v1/admin/sondages/{id}/switch-status`
- **Auth**: Yes (`auth:sanctum`)
- **Description**: Active/désactive un sondage
- **Response**: No-content success

#### Delete Poll
- **Route**: `DELETE /api/v1/admin/sondages/{id}`
- **Auth**: Yes (`auth:sanctum`)
- **Description**: Supprime définitivement un sondage et son image associée

---

## Rencontres

### Public Endpoints

#### Get All Matches
- **Route**: `GET /api/v1/rencontres`
- **Auth**: No
- **Filters** (query params):
  - `init_sondage_id`: Filtre les rencontres par sondage (number)
- **Response**:
```json
{
  "status": 1,
  "message": "Liste des rencontres chargée avec succès.",
  "data": [
    {
      "id": 1,
      "init_sondage_id": 1,
      "homeTeam": {
        "id": 1,
        "libelle": "Équipe A"
      },
      "awayTeam": {
        "id": 2,
        "libelle": "Équipe B"
      },
      "score_home": 2,
      "score_away": 1,
      "winner": {
        "id": 1,
        "libelle": "Équipe A"
      },
      "date_rencontre": "2024-07-01 15:00:00"
    }
  ]
}
```

#### Get Single Match
- **Route**: `GET /api/v1/rencontres/{id}`
- **Auth**: No
- **Parameters**:
  - `id`: ID de la rencontre (number)

### Admin Endpoints (Protected by `auth:sanctum`)

#### Create Match
- **Route**: `POST /api/v1/admin/rencontres`
- **Auth**: Yes (`auth:sanctum`)
- **Payload**:
```json
{
  "init_sondage_id": 1,
  "home_team_id": 1,
  "away_team_id": 2,
  "score_home": 0,
  "score_away": 0,
  "winner_id": null,
  "date_rencontre": "2024-07-01 15:00:00"
}
```

#### Update Match
- **Route**: `PUT /api/v1/admin/rencontres/{id}`
- **Auth**: Yes (`auth:sanctum`)
- **Parameters**:
  - `id`: ID de la rencontre (number)
- **Payload**: Même que création (tous champs optionnels)

#### Delete Match
- **Route**: `DELETE /api/v1/admin/rencontres/{id}`
- **Auth**: Yes (`auth:sanctum`)
- **Description**: Supprime définitivement une rencontre

---

## Votants

### Public Endpoints

#### Register Voter
- **Route**: `POST /api/v1/votants`
- **Auth**: No
- **Payload**:
```json
{
  "name": "John Doe",
  "telephone": "224620123456"
}
```

### Admin Endpoints (Protected by `auth:sanctum`)

#### Get All Voters
- **Route**: `GET /api/v1/admin/votants`
- **Auth**: Yes (`auth:sanctum`)

#### Get Single Voter
- **Route**: `GET /api/v1/admin/votants/{id}`
- **Auth**: Yes (`auth:sanctum`)
- **Parameters**:
  - `id`: ID du votant (number)

#### Update Voter
- **Route**: `PUT /api/v1/admin/votants/{id}`
- **Auth**: Yes (`auth:sanctum`)
- **Parameters**:
  - `id`: ID du votant (number)
- **Payload**: Même que création (tous champs optionnels)

#### Delete Voter
- **Route**: `DELETE /api/v1/admin/votants/{id}`
- **Auth**: Yes (`auth:sanctum`)
- **Description**: Supprime définitivement un votant

---

## Votes

### Public Endpoints

#### Submit Vote
- **Route**: `POST /api/v1/votes`
- **Auth**: No
- **Payload**:
```json
{
  "name": "John Doe",
  "telephone": "224620123456",
  "init_sondage_id": 1,
  "scenario": {
    "quarter_final": {
      "match1": {
        "winner": 1,
        "score": "2-1"
      }
    }
  }
}
```
- **Description**:
  - Si le votant n'existe pas, il est automatiquement créé
  - Si le votant existe déjà (même téléphone), il est récupéré
  - Un événement `SendMessageEvent` est dispatché pour envoyer un SMS de confirmation avec la référence du vote
- **Response**:
```json
{
  "status": 1,
  "message": "Vote enregistré avec succès.",
  "data": {
    "id": 1,
    "reference": "VOT-2024ABCD",
    "votant": {
      "id": 1,
      "name": "John Doe",
      "telephone": "224620123456"
    },
    "init_sondage_id": 1,
    "scenario": { ... },
    "is_winner": false,
    "created_at": "2024-07-01 10:00:00"
  }
}
```

### Admin Endpoints (Protected by `auth:sanctum`)

#### Get All Votes
- **Route**: `GET /api/v1/admin/votes`
- **Auth**: Yes (`auth:sanctum`)
- **Filters** (query params):
  - `init_sondage_id`: Filtre les votes par sondage (number)

#### Get Single Vote
- **Route**: `GET /api/v1/admin/votes/{id}`
- **Auth**: Yes (`auth:sanctum`)
- **Parameters**:
  - `id`: ID du vote (number)

