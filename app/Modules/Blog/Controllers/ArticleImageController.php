<?php

namespace App\Modules\Blog\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Blog\Models\ArticleImage;
use App\Traits\ApiResponses;
use App\Traits\CloudflareUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

// Gestion des images insérées dans le contenu (description) des articles via CKEditor
class ArticleImageController extends Controller
{
    use ApiResponses, CloudflareUpload;

    // Route admin — appelée automatiquement par l'upload adapter de CKEditor
    // à chaque image insérée dans l'éditeur, AVANT la sauvegarde de l'article
    public function store(Request $request)
    {
        // CKEditor (SimpleUploadAdapter) envoie le fichier dans le champ "upload"
        $request->validate([
            'upload' => ['required', 'image', 'mimes:png,jpg,jpeg,webp,gif', 'max:2048'],
        ]);

        $imageName = $this->uploadImage($request->file('upload'), ArticleImage::STORAGE_PATH);

        // article_id NULL : l'article n'existe pas encore, le rattachement
        // se fait dans ArticleController@store ou @update
        ArticleImage::create(['path' => $imageName]);

        // Format de réponse attendu par CKEditor : { "url": "..." } à la racine.
        // L'URL pointe vers notre route publique show() et n'expire jamais,
        // contrairement aux URLs signées R2 (valables 7 jours)
        return response()->json([
            'url' => url('/api/v1/article-images/' . $imageName),
        ]);
    }

    // Route publique — sert une image de contenu référencée dans le HTML d'une description.
    // Redirige vers une URL signée R2 fraîche, générée au moment de la requête
    public function show(string $image)
    {
        $fullPath = 'images/' . ArticleImage::STORAGE_PATH . '/' . $image;

        if (! Storage::disk($this->disk)->exists($fullPath)) {
            return $this->errorResponse("Image introuvable");
        }

        return redirect()->away($this->getImageUrl($image, ArticleImage::STORAGE_PATH));
    }
}
