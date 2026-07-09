<?php

namespace App\Modules\Blog\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Blog\Models\Article;
use App\Modules\Blog\Models\ArticleImage;
use App\Modules\Blog\Requests\ArticleRequest;
use App\Modules\Blog\Resources\ArticleResource;
use App\Traits\ApiResponses;
use App\Traits\CloudflareUpload;
use Illuminate\Support\Facades\Auth;
use Stevebauman\Purify\Facades\Purify;

// Gestion des articles du blog (lecture publique, écriture admin)
class ArticleController extends Controller
{
    use ApiResponses, CloudflareUpload;

    // Route publique — seuls les articles actifs sont visibles sur le site
    public function index()
    {
        $articles = Article::with('tags', 'createdBy', 'updatedBy')
            ->where('status', true)
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->successResponse(ArticleResource::collection($articles), "Liste des articles chargée avec succès.");
    }

    // Route admin — liste tous les articles, y compris les désactivés
    public function adminIndex()
    {
        $articles = Article::with('tags', 'createdBy', 'updatedBy')->orderBy('created_at', 'desc')->get();

        return $this->successResponse(ArticleResource::collection($articles), "Liste des articles chargée avec succès.");
    }

    // Route admin — seul un utilisateur authentifié peut créer un article
    public function store(ArticleRequest $request)
    {
        $data = $request->validated();
        $data['created_by'] = Auth::id();

        // Nettoyage anti-XSS du HTML produit par CKEditor (liste blanche dans config/purify.php)
        $data['description'] = Purify::clean($data['description']);

        // On extrait les tags avant create() car tags n'est pas une colonne de la table articles
        $tags = $data['tags'] ?? [];
        unset($data['tags']);

        // Upload de l'image de couverture sur Cloudflare R2 si fournie
        if ($request->hasFile('cover')) {
            $data['cover_path'] = $this->uploadImage($request->file('cover'), 'articles');
        }

        $article = Article::create($data);

        // Synchronise toujours les tags — un tableau vide détache tous les tags existants
        $article->tags()->sync($tags);

        // Rattache à l'article les images de contenu uploadées par CKEditor pendant la rédaction
        $this->syncContentImages($article);

        logActivity("Création d'un article", $data, $article);

        return $this->successResponse(new ArticleResource($article->load('tags', 'createdBy')), "Article créé avec succès.");
    }

    // Route publique — un article désactivé répond 404, ses commentaires désactivés sont exclus
    public function show(string $id)
    {
        $article = Article::with(['tags', 'createdBy', 'updatedBy', 'comments' => fn ($query) => $query->where('status', true)])
            ->where('status', true)
            ->find($id);

        if (! $article) {
            return $this->errorResponse("Article introuvable");
        }

        return $this->successResponse(new ArticleResource($article), "Article chargé avec succès.");
    }

    // Route admin — détail d'un article même désactivé, avec tous ses commentaires
    public function adminShow(string $id)
    {
        $article = Article::with('tags', 'comments', 'createdBy', 'updatedBy')->find($id);

        if (! $article) {
            return $this->errorResponse("Article introuvable");
        }

        return $this->successResponse(new ArticleResource($article), "Article chargé avec succès.");
    }

    // Route admin — modification d'un article existant
    public function update(ArticleRequest $request, string $id)
    {
        $article = Article::find($id);

        if (! $article) {
            return $this->errorResponse("Article introuvable");
        }

        $data = $request->validated();
        $data['updated_by'] = Auth::id();

        // Nettoyage anti-XSS du HTML produit par CKEditor (liste blanche dans config/purify.php)
        if (isset($data['description'])) {
            $data['description'] = Purify::clean($data['description']);
        }

        // On extrait les tags avant update() car tags n'est pas une colonne de la table articles
        $tags = $data['tags'] ?? [];
        unset($data['tags']);

        if ($request->hasFile('cover')) {
            // Suppression de l'ancienne image avant upload pour éviter les fichiers orphelins sur R2
            if ($article->cover_path) {
                $this->deleteImage($article->cover_path, 'articles');
            }
            $data['cover_path'] = $this->uploadImage($request->file('cover'), 'articles');
        }

        // Capture l'état avant modification pour conserver un historique fidèle dans les logs
        $logData = [
            'old_value' => $article->toArray(),
            'new_value' => $data,
        ];

        $article->update($data);

        // Resynchronise toujours les tags — un tableau vide détache tous les tags existants
        $article->tags()->sync($tags);

        // Rattache les nouvelles images de contenu et supprime celles retirées du texte
        $this->syncContentImages($article);

        logActivity("Modification d'un article", $logData, $article);

        return $this->successResponse(new ArticleResource($article->load('tags', 'createdBy', 'updatedBy')), "Article modifié avec succès.");
    }

    // Route admin — rend l'article visible/non visible sur le site
    public function switchStatus(string $id)
    {
        $article = Article::find($id);

        if (! $article) {
            return $this->errorResponse("Article introuvable");
        }

        $article->update(['status' => ! $article->status, 'updated_by' => Auth::id()]);

        logActivity("Changement de statut d'un article", ['status' => $article->status], $article);

        return $this->successResponse(new ArticleResource($article), $article->status ? "Article activé avec succès." : "Article désactivé avec succès.");
    }

    // Route admin — suppression définitive d'un article et de son image associée
    public function destroy(string $id)
    {
        $article = Article::find($id);

        if (! $article) {
            return $this->errorResponse("Article introuvable");
        }

        // Suppression du fichier image sur R2 pour ne pas laisser de fichiers orphelins
        if ($article->cover_path) {
            $this->deleteImage($article->cover_path, 'articles');
        }

        // Suppression des images de contenu (description) sur R2 et dans le registre
        foreach ($article->images as $image) {
            $this->deleteImage($image->path, ArticleImage::STORAGE_PATH);
        }
        $article->images()->delete();

        logActivity("Suppression d'un article", $article->toArray(), $article);
        $article->delete();

        return $this->noContentSuccessResponse("Article supprimé avec succès");
    }

    // Synchronise le registre article_images avec les images réellement présentes
    // dans le HTML de la description
    private function syncContentImages(Article $article): void
    {
        $referenced = $this->extractContentImageNames($article->description);

        // Supprime de R2 et du registre les images de l'article qui ne sont plus dans le texte
        foreach ($article->images()->whereNotIn('path', $referenced)->get() as $image) {
            $this->deleteImage($image->path, ArticleImage::STORAGE_PATH);
            $image->delete();
        }

        // Rattache les images fraîchement uploadées par CKEditor (article_id encore NULL)
        if ($referenced) {
            ArticleImage::whereNull('article_id')
                ->whereIn('path', $referenced)
                ->update(['article_id' => $article->id]);
        }
    }

    // Extrait les noms de fichiers des balises <img src=".../article-images/xxx.ext"> du HTML
    private function extractContentImageNames(?string $html): array
    {
        if (! $html) {
            return [];
        }

        preg_match_all('#/article-images/([A-Za-z0-9\-]+\.[A-Za-z0-9]+)#', $html, $matches);

        return array_values(array_unique($matches[1]));
    }
}
