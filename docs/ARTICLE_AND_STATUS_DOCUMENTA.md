# Article And Status Documentation

Generated from the current Laravel source code on `2026-06-30`.

## Overview

- Base API prefix: `/api`
- Article endpoints live under:
    - `/api/v1/articles/*` for public read
    - `/api/v1/admin/articles/*` for admin CRUD
    - `/api/v1/article-images/*` for public article-description image lookup
    - `/api/v1/admin/article-images/*` and `/api/v1/admin/articles/content-images` for admin image management
- Protected endpoints require `Authorization: Bearer <sanctum_token>`
- This document focuses on:
    - articles
    - article description image workflow
    - all `switchStatus` endpoints across modules

## Shared Response Envelopes

### Standard Success

```json
{
    "status": 1,
    "message": "Operation completed successfully.",
    "data": {}
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

Validation errors are standard Laravel `422` responses:

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "upload": ["The upload field is required."]
    }
}
```

---

## Article Data Model

Article responses expose the following fields:

```json
{
    "id": 7,
    "title": "Launching a New Angular Frontend",
    "short_description": "How the SPA consumes the backend API.",
    "description": "<p>Rich HTML content...</p>",
    "cover_url": "https://cdn.example.com/articles/angular-spa-cover.jpg",
    "status": true,
    "tags": [
        {
            "id": 2,
            "libelle": "Frontend",
            "description": "Frontend related content",
            "status": true
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
```

## Article Workflow

### Public Behavior

- Public list returns only active articles.
- Public detail returns only active articles.
- Public detail returns only active comments.

### Admin Behavior

- Admin list returns active and inactive articles.
- Admin detail returns active and inactive articles.
- Admin create/update accepts rich HTML in `description`.
- The backend sanitizes `description` HTML before saving it.
- Cover upload uses `multipart/form-data`.

### Article Validation

#### Create / Update Rules

```json
{
    "title": "required|string|min:2|max:200",
    "short_description": "nullable|string",
    "description": "required|string|min:2",
    "cover": "nullable|image|mimes:png,jpg,jpeg,webp|max:2048",
    "tags": "nullable|array",
    "tags.*": "integer|exists:tags,id"
}
```

---

## Article Endpoints

### GET `/api/v1/articles`

- Auth: public
- Payload example: none
- Purpose: list public active articles only
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
            "description": "<p>Rich HTML content...</p>",
            "cover_url": "https://cdn.example.com/articles/angular-spa-cover.jpg",
            "status": true,
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
- Purpose: fetch one active article and only active comments
- Success response example:

```json
{
    "status": 1,
    "message": "Article chargé avec succès.",
    "data": {
        "id": 7,
        "title": "Launching a New Angular Frontend",
        "short_description": "How the SPA consumes the backend API.",
        "description": "<h2>Introduction</h2><p>Rich HTML content...</p><p><img src=\"https://cdn.example.com/articles/content/img-123.jpg\" data-article-image-id=\"18\"></p>",
        "cover_url": "https://cdn.example.com/articles/angular-spa-cover.jpg",
        "status": true,
        "tags": [
            {
                "id": 2,
                "libelle": "Frontend",
                "description": "Frontend related content",
                "status": true
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

- Not found example:

```json
{
    "status": 0,
    "message": "Article introuvable",
    "error": []
}
```

### GET `/api/v1/admin/articles`

- Auth: Bearer token required
- Payload example: none
- Purpose: list all articles, including inactive ones
- Success response example: same schema as public article list, but includes inactive articles too.

### GET `/api/v1/admin/articles/{id}`

- Auth: Bearer token required
- Payload example: none
- Purpose: fetch one article even if inactive, with all comments
- Success response example: same schema as article detail, but includes inactive articles and all related comments.

### POST `/api/v1/admin/articles`

- Auth: Bearer token required
- Content-Type: `multipart/form-data`
- Purpose: create an article
- Payload example:

```json
{
    "title": "Launching a New Angular Frontend",
    "short_description": "How the SPA consumes the backend API.",
    "description": "<h2>Introduction</h2><p>Rich HTML from CKEditor/Quill/Summernote...</p><p><img src=\"https://cdn.example.com/articles/content/img-123.jpg\" data-article-image-id=\"18\"></p>",
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
        "description": "<h2>Introduction</h2><p>Rich HTML from CKEditor/Quill/Summernote...</p><p><img src=\"https://cdn.example.com/articles/content/img-123.jpg\" data-article-image-id=\"18\"></p>",
        "cover_url": "https://cdn.example.com/articles/angular-spa-cover.jpg",
        "status": true,
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
- Purpose: update an existing article
- Payload example:

```json
{
    "title": "Launching a New Angular Frontend v2",
    "short_description": "Updated summary.",
    "description": "<h2>Updated section</h2><p>Updated rich HTML...</p><p><img src=\"https://cdn.example.com/articles/content/img-456.jpg\" data-article-image-id=\"22\"></p>",
    "cover": "<binary image file>",
    "tags": [2, 6]
}
```

- Success response example: same schema as article create response, with updated values.

### PATCH `/api/v1/admin/articles/{id}/switch-status`

- Auth: Bearer token required
- Payload example: none
- Purpose: toggle `article.status`
- Success response example when article becomes active:

```json
{
    "status": 1,
    "message": "Article activé avec succès.",
    "data": {
        "id": 7,
        "title": "Launching a New Angular Frontend",
        "short_description": "How the SPA consumes the backend API.",
        "description": "<p>Rich HTML content...</p>",
        "cover_url": "https://cdn.example.com/articles/angular-spa-cover.jpg",
        "status": true,
        "tags": [],
        "comments": [],
        "created_by": "Admin User",
        "updated_by": "Admin User",
        "created_at": "30-06-2026 10:00:00",
        "updated_at": "30-06-2026 11:00:00"
    }
}
```

- Success response example when article becomes inactive:

```json
{
    "status": 1,
    "message": "Article désactivé avec succès.",
    "data": {
        "id": 7,
        "title": "Launching a New Angular Frontend",
        "short_description": "How the SPA consumes the backend API.",
        "description": "<p>Rich HTML content...</p>",
        "cover_url": "https://cdn.example.com/articles/angular-spa-cover.jpg",
        "status": false,
        "tags": [],
        "comments": [],
        "created_by": "Admin User",
        "updated_by": "Admin User",
        "created_at": "30-06-2026 10:00:00",
        "updated_at": "30-06-2026 11:00:00"
    }
}
```

### DELETE `/api/v1/admin/articles/{id}`

- Auth: Bearer token required
- Payload example: none
- Purpose: delete article, cover image, and linked description images
- Success response example:

```json
{
    "status": 1,
    "message": "Article supprimé avec succès"
}
```

---

## Article Description Images

This section is the important frontend integration part for rich-text editors such as:

- CKEditor
- Quill
- Summernote
- TinyMCE
- any custom WYSIWYG editor

### Goal

When the frontend user inserts an image inside the article `description` field:

1. the editor uploads the file to `POST /api/v1/admin/articles/content-images`
2. the backend returns:
    - an image `id`
    - an image `path`
3. the frontend inserts `<img src="...">` into the HTML using the returned `path`
4. the frontend should also keep the returned `id`
5. if the user removes that image from the editor before or after saving the article, the frontend can call `DELETE /api/v1/admin/article-images/{id}`

### Store Endpoint

#### POST `/api/v1/admin/articles/content-images`

- Auth: Bearer token required
- Content-Type: `multipart/form-data`
- Purpose: upload one image used inside the `description` HTML
- Expected file field name: `upload`
- Validation:

```json
{
    "upload": "required|image|mimes:png,jpg,jpeg,webp,gif|max:2048"
}
```

- Payload example:

```json
{
    "upload": "<binary image file>"
}
```

- Success response example:

```json
{
    "status": 1,
    "message": "Image uploadée avec succès",
    "data": {
        "id": 18,
        "path": "https://cdn.example.com/articles/content/img-123.jpg"
    }
}
```

### What The Frontend Must Do After Upload

- Use `data.path` as the `<img src>` value inside the editor HTML.
- Keep `data.id` in frontend state.
- Best practice: also inject the id into the DOM as a custom attribute so the editor content remains traceable.

Recommended inserted HTML:

```html
<img
    src="https://cdn.example.com/articles/content/img-123.jpg"
    data-article-image-id="18"
    alt="Article image"
/>
```

This makes deletion easier because the frontend can read `data-article-image-id` when the image node is removed.

### Public Lookup Endpoint

#### GET `/api/v1/article-images/{image}`

- Auth: public
- Purpose: fetch image metadata and a usable path for one uploaded description image
- Route parameter: `{image}` is the article image record id
- Success response example:

```json
{
    "status": 1,
    "message": "Image chargée avec succès.",
    "data": {
        "id": 18,
        "path": "https://cdn.example.com/articles/content/img-123.jpg"
    }
}
```

- Not found example:

```json
{
    "status": 0,
    "message": "Image introuvable",
    "error": []
}
```

### Delete Endpoint

#### DELETE `/api/v1/admin/article-images/{image}`

- Auth: Bearer token required
- Purpose: remove one uploaded description image record
- Route parameter: `{image}` is the image id returned by the upload endpoint
- Payload example: none
- Success response example:

```json
{
    "status": 1,
    "message": "Image supprimée avec succès."
}
```

### Recommended Frontend Integration Flow

#### Before Article Save

- The user types formatted HTML in the editor.
- When the user inserts an image:
    - upload that file immediately to `POST /api/v1/admin/articles/content-images`
    - read `response.data.id`
    - read `response.data.path`
    - insert `<img src="response.data.path" data-article-image-id="response.data.id">` into the editor

#### During Article Save

- Submit the final editor HTML as the `description` field to:
    - `POST /api/v1/admin/articles`
    - or `PUT /api/v1/admin/articles/{id}`
- The backend will sanitize the HTML and link uploaded content images to the saved article.

#### When The User Removes An Image From The Editor

- Detect that the removed node had a `data-article-image-id`.
- Call:

```bash
DELETE /api/v1/admin/article-images/{id}
```

- Remove the node from the editor HTML.

### Angular-Friendly Pseudo Flow

```ts
async function uploadArticleDescriptionImage(
    file: File,
): Promise<{ id: number; path: string }> {
    const formData = new FormData();
    formData.append("upload", file);

    const response = await http
        .post("/api/v1/admin/articles/content-images", formData)
        .toPromise();

    return {
        id: response.data.data.id,
        path: response.data.data.path,
    };
}

async function onEditorImageInserted(file: File, editor: any) {
    const image = await uploadArticleDescriptionImage(file);

    editor.insertHtml(
        `<img src="${image.path}" data-article-image-id="${image.id}" alt="Article image">`,
    );
}

async function onEditorImageRemoved(imageId: number) {
    await http.delete(`/api/v1/admin/article-images/${imageId}`).toPromise();
}
```

### Important Frontend Notes

- The upload endpoint returns the image URL inside `data.path`, not at the top-level `url`.
- If your editor expects `{ url: "..." }`, you must adapt the response manually in the frontend.
- The backend stores description images before the article is saved, then links them later when the article is created or updated.
- The returned image `id` should be treated as the backend reference for later deletion.
- The safest pattern is:
    - store the image id in the DOM
    - store the image id in editor state
    - call delete when the image is removed from the rich-text content

### Current Backend Caveats

- The current implementation is clearly intended for editor-driven description images.
- There is a code-level mismatch between:
    - HTML parsing that expects `/article-images/<filename>`
    - the public show endpoint which currently looks up by image id
- Also, `DELETE /api/v1/admin/article-images/{image}` currently deletes the database row but does not explicitly remove the file from storage in that controller method.
- This documentation describes the current behavior exactly as implemented plus the intended frontend usage you requested.

---

## All switchStatus Endpoints

All endpoints below require Bearer authentication.

## Blog Status Endpoints

### PATCH `/api/v1/admin/articles/{id}/switch-status`

- Toggles: `article.status`
- Success response: returns full article resource
- Example success message:
    - `Article activé avec succès.`
    - `Article désactivé avec succès.`

### PATCH `/api/v1/admin/comments/{id}/switch-status`

- Toggles: `comment.status`
- Success response: returns comment resource
- Example success message:
    - `Commentaire activé avec succès.`
    - `Commentaire désactivé avec succès.`

## Contact Status Endpoints

### PATCH `/api/v1/admin/contacts/{id}/switch-status`

- Toggles: `contact.status`
- Success response example:

```json
{
    "status": 1,
    "message": "Message de contact activé avec succès.",
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

## Offer Status Endpoints

### PATCH `/api/v1/admin/offer-types/{id}/switch-status`

- Toggles: `offer_type.status`
- Success response: returns offer type resource
- Example success message:
    - `Type d'offre activé avec succès.`
    - `Type d'offre désactivé avec succès.`

### PATCH `/api/v1/admin/offers/{id}/switch-status`

- Toggles: `offer.status`
- Success response: returns offer resource
- Example success message:
    - `Offre activée avec succès.`
    - `Offre désactivée avec succès.`

## Settings Status Endpoints

### GET `/api/v1/admin/categories/{id}/category`

- Toggles: `category.status`
- Success response example:

```json
{
    "status": 1,
    "message": "Status de la catégorie mis a jour avec succès"
}
```

### GET `/api/v1/admin/tags/{id}/tag`

- Toggles: `tag.status`
- Success response example:

```json
{
    "status": 1,
    "message": "Status de la tag mis a jour avec succès"
}
```

## Website Status Endpoints

### GET `/api/v1/admin/clients/{id}/status`

- Toggles: `client.status`
- Success response example:

```json
{
    "status": 1,
    "message": "Statut du client mis à jour avec succès."
}
```

### GET `/api/v1/admin/partners/{id}/status`

- Toggles: `partner.status`
- Success response example:

```json
{
    "status": 1,
    "message": "Statut du partenaire mis à jour avec succès."
}
```

### GET `/api/v1/admin/projects/{id}/status`

- Toggles: `project.status`
- Success response example:

```json
{
    "status": 1,
    "message": "Statut du projet mis à jour avec succès."
}
```

### GET `/api/v1/admin/services/{id}/status`

- Toggles: `service.status`
- Success response example:

```json
{
    "status": 1,
    "message": "Statut du service mis à jour avec succès."
}
```

### GET `/api/v1/admin/statistics/{id}/status`

- Toggles: `statistic.status`
- Success response example:

```json
{
    "status": 1,
    "message": "Statut de la statistique mis à jour avec succès."
}
```

### GET `/api/v1/admin/testimonials/{id}/status`

- Toggles: `testimonial.status`
- Success response example:

```json
{
    "status": 1,
    "message": "Statut du témoignage mis à jour avec succès."
}
```

### GET `/api/v1/admin/visions/{id}/status`

- Toggles: `vision.status`
- Success response example:

```json
{
    "status": 1,
    "message": "Statut de la vision mis à jour avec succès."
}
```

## Jobs Status Endpoints

### PATCH `/api/admin/jobs/developer-moments/{developerMoment}/switch-status`

- Toggles: `developer_moment.is_active`
- Success response example:

```json
{
    "status": 1,
    "message": "Statut du developer moment mis à jour avec succès."
}
```

### PATCH `/api/admin/jobs/pages/{page}/switch-status`

- Toggles: `page.is_active`
- Success response example:

```json
{
    "status": 1,
    "message": "Statut de la page mis à jour avec succès."
}
```

### PATCH `/api/admin/jobs/heroes/{hero}/switch-status`

- Toggles: `hero.is_active`
- Success response example:

```json
{
    "status": 1,
    "message": "Statut du hero mis à jour avec succès."
}
```

### PATCH `/api/admin/jobs/openings/{opening}/switch-status`

- Toggles: `job_opening.is_active`
- Success response example:

```json
{
    "status": 1,
    "message": "Statut de l'offre d'emploi mis à jour avec succès."
}
```

### PATCH `/api/admin/jobs/newsletters/{newsletter}/switch-status`

- Toggles: `newsletter.is_subscribed`
- Success response example:

```json
{
    "status": 1,
    "message": "Statut de l'abonnement newsletter mis à jour avec succès."
}
```

---

## Recommended Frontend Rules

- For article cover image:
    - upload through article create/update request in `cover`
- For article description images:
    - upload separately through `POST /api/v1/admin/articles/content-images`
    - use returned `data.path` inside the HTML
    - keep returned `data.id` for deletion
- For rich text image nodes:
    - always add `data-article-image-id`
- For status toggles:
    - treat them as mutation endpoints even when some modules use `GET`
    - refresh the resource or update the local status immediately after success

## Quick Checklist For Angular Frontend

- Use `FormData` for `cover` and `upload` file fields.
- Send rich editor HTML as `description`.
- On image upload:
    - call content-image store
    - inject returned `path` into `<img src>`
    - inject returned `id` into `data-article-image-id`
- On image removal:
    - read `data-article-image-id`
    - call delete endpoint
- On article save:
    - submit final HTML to article create/update endpoint
- On status switch:
    - call the route for the related module
    - update the local item state from response or refresh the list
