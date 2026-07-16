# Analytics Module Documentation

## Overview

Ce module permet de gérer l'analyse de la navigation sur le site web (SPA Angular). Il inclut le suivi des visiteurs via un système d'événements et un listener en file d'attente pour de meilleures performances.

## Architecture

Le module fonctionne avec un système d'événement/écouteur (Event/Listener) pour traiter les requêtes de tracking de manière asynchrone via la file d'attente Laravel.

- **Event**: `AnalyticEvent` - Reçoit les données de tracking depuis le frontend
- **Listener**: `AnalyticListener` - Traite les données (User-Agent, géolocalisation IP) et stocke en base de données
- **Packages Utilisés**:
    - `jenssegers/agent`: pour détecter le device, le navigateur et l'OS
    - `torann/geoip`: pour la géolocalisation IP (pays)
- **Anonymisation**: L'IP n'est pas stockée en clair, mais hachée avec `sha256` + `app.key`

---

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

## Endpoints Backend

### Public Endpoints

#### Track Pageview

- **Route**: `POST /api/analytics/track`
- **Auth**: No
- **Description**: Permet au frontend de track une vue de page (un événement)
- **Payload**:

```json
{
    "visitor_id": "550e8400-e29b-41d4-a716-446655440000", // UUID unique par visiteur (persistant via localStorage)
    "path": "/accueil",
    "referrer": "https://google.com"
}
```

- **Description du Payload**:
    - `visitor_id`: Identifiant unique du visiteur, généré par le frontend et stocké dans localStorage
    - `path`: Chemin de la page visitée (Angular route)
    - `referrer`: URL de provenance (ou chaîne vide si aucune)
- **Response**: No-content success (204 No Content)
- **Important**: Pour que le tracking fonctionne même si l'utilisateur ferme l'onglet juste après, utiliser `navigator.sendBeacon` du côté frontend (voir la section Frontend Integration ci-dessous).

### Admin Endpoints (Protected by `auth:sanctum`)

#### Get Analytics

- **Route**: `GET /api/analytics`
- **Auth**: Yes (`auth:sanctum`)
- **Filters** (query params):
    - `date_debut`: Date de début (optionnelle, format YYYY-MM-DD)
    - `date_fin`: Date de fin (optionnelle, format YYYY-MM-DD)
- **Description**:
    - Si ni `date_debut` ni `date_fin` ne sont fournis, retourne les analytics du jour (`Carbon::today()`)
    - Si seule `date_debut` est fournie, retourne les analytics depuis cette date
    - Si seule `date_fin` est fournie, retourne les analytics jusqu'à cette date
    - Si les deux sont fournies, retourne les analytics dans l'intervalle
- **Response**:

```json
{
    "status": 1,
    "message": "Analytics du site web chargé avec succès.",
    "data": [
        {
            "id": 1,
            "visitor_id": "550e8400-e29b-41d4-a716-446655440000",
            "path": "/accueil",
            "referrer": "https://google.com",
            "device": "desktop", // mobile | tablet | desktop
            "browser": "Chrome",
            "os": "Windows 10",
            "country": "GN", // ISO 3166-1 alpha-2
            "ip_hash": "a1b2c3d4e5f6...",
            "created_at": "2026-07-16 10:00:00",
            "updated_at": "2026-07-16 10:00:00"
        }
    ]
}
```

---

## Frontend Integration (Angular)

### Pourquoi cette architecture ?

Angular est une Single Page Application (SPA), donc il ne recharge pas la page à chaque navigation. Le middleware Laravel classique ne voit que les appels API et ne peut pas détecter les "vues de page" Angular. Il faut donc que l'Angular envoie explicitement un événement à chaque changement de route.

### Étape 1 : Créer un service Analytics

Créez un service `analytics.service.ts` pour gérer le tracking :

```typescript
// src/app/core/services/analytics.service.ts
import { Injectable } from "@angular/core";
import { NavigationEnd, Router } from "@angular/router";
import { filter } from "rxjs";

@Injectable({
    providedIn: "root",
})
export class AnalyticsService {
    constructor(private router: Router) {}

    /**
     * Récupère (ou crée) un identifiant visiteur persistant
     */
    private getVisitorId(): string {
        let id = localStorage.getItem("visitor_id");
        if (!id) {
            id = crypto.randomUUID();
            localStorage.setItem("visitor_id", id);
        }
        return id;
    }

    /**
     * Envoie un événement de pageview à l'API backend
     */
    private trackPageview(path: string): void {
        const payload = {
            visitor_id: this.getVisitorId(),
            path,
            referrer: document.referrer || "",
        };

        // Utilise navigator.sendBeacon pour garantir l'envoi même si l'utilisateur ferme l'onglet
        navigator.sendBeacon(
            "/api/analytics/track",
            new Blob([JSON.stringify(payload)], { type: "application/json" }),
        );
    }

    /**
     * Initialise le tracking automatique sur les changements de route Angular
     */
    initPageTracking(): void {
        this.router.events
            .pipe(
                filter(
                    (event): event is NavigationEnd =>
                        event instanceof NavigationEnd,
                ),
            )
            .subscribe((event) => {
                this.trackPageview(event.urlAfterRedirects);
            });
    }
}
```

### Étape 2 : Initialiser le service dans AppComponent

Branchez le service dans votre `AppComponent` pour activer le tracking automatique :

```typescript
// src/app/app.component.ts
import { Component, OnInit } from "@angular/core";
import { AnalyticsService } from "./core/services/analytics.service";

@Component({
    selector: "app-root",
    templateUrl: "./app.component.html",
    styleUrls: ["./app.component.css"],
})
export class AppComponent implements OnInit {
    constructor(private analyticsService: AnalyticsService) {}

    ngOnInit(): void {
        this.analyticsService.initPageTracking();
    }
}
```

### Notes importantes pour le frontend

- `navigator.sendBeacon` est préféré à `fetch` ou `axios.post` car il est conçu pour des requêtes "fire-and-forget" qui doivent réussir même si l'utilisateur ferme immédiatement l'onglet.
- Le `visitor_id` est stocké dans `localStorage` pour identifier le même visiteur entre les sessions.
- L'API backend renvoie une réponse "no content" (204) pour minimiser la latence.

---

## Base de données

La table `analytics` stocke les données de tracking :

| Colonne      | Type                  | Description                                         |
| ------------ | --------------------- | --------------------------------------------------- |
| `id`         | BIGINT unsigned       | Clé primaire                                        |
| `visitor_id` | UUID                  | Identifiant unique du visiteur                      |
| `path`       | VARCHAR(255)          | Chemin de la page visitée                           |
| `referrer`   | VARCHAR(255) nullable | URL de provenance                                   |
| `device`     | VARCHAR(20)           | Type de device (mobile/tablet/desktop)              |
| `browser`    | VARCHAR(50)           | Navigateur                                          |
| `os`         | VARCHAR(50)           | Système d'exploitation                              |
| `country`    | VARCHAR(2) nullable   | Code pays ISO 3166-1 alpha-2 (ex: GN, FR)           |
| `ip_hash`    | VARCHAR(64)           | Hash SHA-256 de l'IP + app.key (pour anonymisation) |
| `created_at` | TIMESTAMP             | Date/heure de la visite                             |
| `updated_at` | TIMESTAMP             | Date/heure de mise à jour                           |
