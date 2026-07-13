<?php

namespace Tests\Feature\Formation;

use App\Modules\Formation\Models\Formation;
use App\Modules\Formation\Models\FormationImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FormationImageFeatureTest extends FormationTestCase
{
    public function test_admin_can_upload_attach_and_remove_a_content_image(): void
    {
        $this->authenticate();
        $category = $this->createCategory();
        $draftToken = Str::uuid()->toString();

        $upload = $this->post('/api/v1/admin/formations/content-images', [
            'upload' => UploadedFile::fake()->create('ckeditor.jpg', 100, 'image/jpeg'),
            'draft_token' => $draftToken,
        ], ['Accept' => 'application/json'])->assertOk();

        $url = $upload->json('url');
        $image = FormationImage::firstOrFail();
        Storage::disk('r2')->assertExists('images/'.FormationImage::STORAGE_PATH.'/'.$image->image_path);

        $response = $this->postJson('/api/v1/admin/formations', $this->formationPayload($category, [
            'description' => '<p>Texte</p><img src="'.$url.'"><script>alert(1)</script>',
            'draft_token' => $draftToken,
        ]))->assertOk();

        $formation = Formation::findOrFail($response->json('data.id'));
        $this->assertStringNotContainsString('<script', $formation->description);
        $this->assertSame($formation->id, $image->fresh()->formation_id);

        $this->patchJson("/api/v1/admin/formations/{$formation->id}", [
            'description' => '<p>Image retirée</p>',
        ])->assertOk();

        $this->assertDatabaseMissing('formation_images', ['id' => $image->id]);
        Storage::disk('r2')->assertMissing('images/'.FormationImage::STORAGE_PATH.'/'.$image->image_path);
    }

    public function test_content_image_upload_rejects_invalid_files_and_requires_authentication(): void
    {
        $draftToken = Str::uuid()->toString();

        $this->post('/api/v1/admin/formations/content-images', [
            'upload' => UploadedFile::fake()->create('document.txt', 10, 'text/plain'),
            'draft_token' => $draftToken,
        ], ['Accept' => 'application/json'])->assertUnauthorized();

        $this->authenticate();

        $this->post('/api/v1/admin/formations/content-images', [
            'upload' => UploadedFile::fake()->create('document.txt', 10, 'text/plain'),
            'draft_token' => $draftToken,
        ], ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('upload');
    }

    public function test_an_image_cannot_be_attached_with_another_users_draft_token(): void
    {
        $this->authenticate();
        $category = $this->createCategory();
        $draftToken = Str::uuid()->toString();
        $image = FormationImage::create([
            'image_path' => 'foreign.jpg',
            'draft_token' => $draftToken,
            'uploaded_by' => null,
        ]);

        $this->postJson('/api/v1/admin/formations', $this->formationPayload($category, [
            'description' => '<img src="'.url('/api/v1/formation-images/'.$image->image_path).'">',
            'draft_token' => $draftToken,
        ]))->assertUnprocessable()
            ->assertJsonValidationErrors('description');
    }

    public function test_orphan_cleanup_deletes_only_old_unattached_images(): void
    {
        $old = FormationImage::create([
            'image_path' => 'old.jpg',
            'draft_token' => Str::uuid(),
            'uploaded_by' => $this->user->id,
        ]);
        $recent = FormationImage::create([
            'image_path' => 'recent.jpg',
            'draft_token' => Str::uuid(),
            'uploaded_by' => $this->user->id,
        ]);
        $old->forceFill(['created_at' => now()->subDays(2)])->save();
        Storage::disk('r2')->put('images/'.FormationImage::STORAGE_PATH.'/old.jpg', 'old');
        Storage::disk('r2')->put('images/'.FormationImage::STORAGE_PATH.'/recent.jpg', 'recent');

        Artisan::call('formations:clean-orphan-images');

        $this->assertDatabaseMissing('formation_images', ['id' => $old->id]);
        $this->assertDatabaseHas('formation_images', ['id' => $recent->id]);
        Storage::disk('r2')->assertMissing('images/'.FormationImage::STORAGE_PATH.'/old.jpg');
        Storage::disk('r2')->assertExists('images/'.FormationImage::STORAGE_PATH.'/recent.jpg');
    }
}
