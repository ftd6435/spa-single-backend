<?php

namespace App\Console\Commands;

use App\Modules\Blog\Models\ArticleImage;
use App\Traits\CloudflareUpload;
use Illuminate\Console\Command;

// Supprime les images de contenu uploadées via CKEditor mais jamais rattachées à un article
// (rédaction abandonnée avant l'enregistrement). Planifiée quotidiennement dans routes/console.php
class CleanOrphanArticleImages extends Command
{
    use CloudflareUpload;

    protected $signature = 'articles:clean-orphan-images';

    protected $description = "Supprime les images de contenu d'articles orphelines (non rattachées depuis plus de 24h)";

    public function handle(): int
    {
        // Marge de 24h : une image récente peut appartenir à un article en cours de rédaction
        $orphans = ArticleImage::whereNull('article_id')
            ->where('created_at', '<', now()->subDay())
            ->get();

        foreach ($orphans as $orphan) {
            $this->deleteImage($orphan->path, ArticleImage::STORAGE_PATH);
            $orphan->delete();
        }

        $this->info("{$orphans->count()} image(s) orpheline(s) supprimée(s).");

        return self::SUCCESS;
    }
}
