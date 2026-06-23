<?php

namespace App\Modules\Blog\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Blog\Models\Article;
use App\Modules\Blog\Requests\ArticleRequest;
use App\Traits\ApiResponses;
use App\Traits\CloudflareUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ArticleController extends Controller
{
    use ApiResponses, CloudflareUpload;

    public function index()
    {
        $articles = Article::with('tags', 'createdBy', 'updatedBy')->orderBy('created_at', 'desc')->get();

        return $this->successResponse($articles, "Liste des articles chargée avec succès.");
    }

    public function store(ArticleRequest $request)
    {
        $data = $request->validated();
        $data['created_by'] = Auth::id();

        if ($request->hasFile('cover')) {
            $data['cover_path'] = $this->uploadImage($request->file('cover'), 'articles');
        }

        $article = Article::create($data);

        // Synchronise les tags many-to-many via la table pivot article_tag
        if (isset($data['tags'])) {
            $article->tags()->sync($data['tags']);
        }

        logActivity("Création d'un article", $data, $article);

        return $this->successResponse($article, "Article créé avec succès.");
    }

    public function show(string $id)
    {
        $article = Article::with('tags', 'comments', 'createdBy', 'updatedBy')->find($id);

        if (! $article) {
            return $this->errorResponse("Article introuvable");
        }

        return $this->successResponse($article, "Article demandé chargé avec succès");
    }

    public function update(ArticleRequest $request, string $id)
    {
        $article = Article::find($id);

        if (! $article) {
            return $this->errorResponse("Article introuvable");
        }

        $data = $request->validated();
        $data['updated_by'] = Auth::id();

        if ($request->hasFile('cover')) {
            // On supprime l'ancienne image si elle existe, avant d'uploader la nouvelle
            if ($article->cover_path) {
                $this->deleteImage($article->cover_path, 'articles');
            }
            $data['cover_path'] = $this->uploadImage($request->file('cover'), 'articles');
        }

        $logData = [
            'old_value' => $article->toArray(),
            'new_value' => $data,
        ];

        $article->update($data);

        if (isset($data['tags'])) {
            $article->tags()->sync($data['tags']);
        }

        logActivity("Modification d'un article", $logData, $article);

        return $this->successResponse($article, "Article modifié avec succès.");
    }

    public function destroy(string $id)
    {
        $article = Article::find($id);

        if (! $article) {
            return $this->errorResponse("Article introuvable");
        }

        // On supprime aussi le fichier image sur R2 pour ne pas laisser de fichiers orphelins
        if ($article->cover_path) {
            $this->deleteImage($article->cover_path, 'articles');
        }

        logActivity("Suppression d'un article", $article->toArray(), $article);
        $article->delete();

        return $this->noContentSuccessResponse("Article supprimé avec succès");
    }

    // Attache un ou plusieurs tags à un article (sans retirer les tags déjà présents)
    public function attachTags(Request $request, string $articleId)
    {
        $article = Article::find($articleId);

        if (! $article) {
            return $this->errorResponse("Article introuvable");
        }

        $validated = $request->validate([
            'tags'   => ['required', 'array', 'min:1'],
            'tags.*' => ['integer', 'exists:tags,id'],
        ]);

        // syncWithoutDetaching : ajoute les nouveaux tags sans supprimer les existants ni planter sur la contrainte unique
        $article->tags()->syncWithoutDetaching($validated['tags']);

        logActivity("Association de tags à un article", $validated, $article);

        return $this->successResponse($article->load('tags'), "Tags associés avec succès.");
    }

    // Détache un tag précis d'un article
    public function detachTag(string $articleId, string $tagId)
    {
        $article = Article::find($articleId);

        if (! $article) {
            return $this->errorResponse("Article introuvable");
        }

        $article->tags()->detach($tagId);

        logActivity("Retrait d'un tag d'un article", ['tag_id' => $tagId], $article);

        return $this->successResponse($article->load('tags'), "Tag retiré avec succès.");
    }
}