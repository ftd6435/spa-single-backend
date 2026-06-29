<?php

namespace App\Modules\Blog\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Blog\Models\Article;
use App\Modules\Blog\Requests\ArticleRequest;
use App\Modules\Blog\Resources\ArticleResource;
use App\Traits\ApiResponses;
use App\Traits\CloudflareUpload;
use Illuminate\Support\Facades\Auth;

// Gestion des articles du blog (lecture publique, écriture admin)
class ArticleController extends Controller
{
    use ApiResponses, CloudflareUpload;

    // Route publique — tout visiteur peut lister les articles
    public function index()
    {
        // Chargement eager des relations pour éviter le problème N+1
        $articles = Article::with('tags', 'createdBy', 'updatedBy')->orderBy('created_at', 'desc')->get();

        return $this->successResponse(ArticleResource::collection($articles), "Liste des articles chargée avec succès.");
    }

    // Route admin — seul un utilisateur authentifié peut créer un article
    public function store(ArticleRequest $request)
    {
        $data = $request->validated();
        $data['created_by'] = Auth::id();

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

        logActivity("Création d'un article", $data, $article);

        return $this->successResponse(new ArticleResource($article->load('tags', 'createdBy')), "Article créé avec succès.");
    }

    // Route publique — tout visiteur peut lire un article avec ses tags et commentaires
    public function show(string $id)
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

        logActivity("Modification d'un article", $logData, $article);

        return $this->successResponse(new ArticleResource($article->load('tags', 'createdBy', 'updatedBy')), "Article modifié avec succès.");
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

        logActivity("Suppression d'un article", $article->toArray(), $article);
        $article->delete();

        return $this->noContentSuccessResponse("Article supprimé avec succès");
    }
}
