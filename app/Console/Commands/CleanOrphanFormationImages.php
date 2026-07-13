<?php

namespace App\Console\Commands;

use App\Modules\Formation\Models\FormationImage;
use App\Traits\CloudflareUpload;
use Illuminate\Console\Command;

class CleanOrphanFormationImages extends Command
{
    use CloudflareUpload;

    protected $signature = 'formations:clean-orphan-images';

    protected $description = 'Supprime les images de formation non rattachées depuis plus de 24 heures';

    public function handle(): int
    {
        $orphans = FormationImage::whereNull('formation_id')
            ->where('created_at', '<', now()->subDay())
            ->get();

        foreach ($orphans as $orphan) {
            $this->deleteImage($orphan->image_path, FormationImage::STORAGE_PATH);
            $orphan->delete();
        }

        $this->info("{$orphans->count()} image(s) de formation orpheline(s) supprimée(s).");

        return self::SUCCESS;
    }
}
