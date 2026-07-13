<?php

namespace Tests\Feature\Formation;

use App\Modules\Formation\Enums\FormationStatus;
use App\Modules\Formation\Models\Formation;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FormationFeatureTest extends FormationTestCase
{
    public function test_admin_can_create_a_formation_and_public_resource_exposes_frontend_fields(): void
    {
        $this->authenticate();
        $category = $this->createCategory();

        $response = $this->post('/api/v1/admin/formations', $this->formationPayload($category, [
            'thumbnail' => UploadedFile::fake()->create('thumbnail.jpg', 100, 'image/jpeg'),
        ]), ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonPath('data.status', 'en_attente')
            ->assertJsonPath('data.category.id', $category->id);

        $formation = Formation::findOrFail($response->json('data.id'));
        Storage::disk('r2')->assertExists('images/'.Formation::THUMBNAIL_STORAGE_PATH.'/'.$formation->thumbnail_path);

        $this->getJson("/api/v1/formations/{$formation->id}")
            ->assertOk()
            ->assertJsonPath('data.status', 'en_attente')
            ->assertJsonPath('data.nombre_places', 15)
            ->assertJsonPath('data.frais_inscription', '100000.00')
            ->assertJsonPath('data.category.id', $category->id)
            ->assertJsonPath('data.description', '<p>Contenu de la formation</p>')
            ->assertJsonMissingPath('data.thumbnail_path')
            ->assertJsonMissingPath('data.created_by')
            ->assertJsonMissingPath('data.participations');
    }

    public function test_admin_can_update_thumbnail_status_state_and_soft_delete_formation(): void
    {
        $this->authenticate();
        $formation = $this->createFormation(['thumbnail_path' => 'old.jpg']);
        Storage::disk('r2')->put('images/'.Formation::THUMBNAIL_STORAGE_PATH.'/old.jpg', 'old');

        $this->post("/api/v1/admin/formations/{$formation->id}", [
            '_method' => 'PATCH',
            'libelle' => 'Laravel expert',
            'thumbnail' => UploadedFile::fake()->create('new.jpg', 100, 'image/jpeg'),
        ], ['Accept' => 'application/json'])->assertOk()
            ->assertJsonPath('data.libelle', 'Laravel expert');

        $formation->refresh();
        Storage::disk('r2')->assertMissing('images/'.Formation::THUMBNAIL_STORAGE_PATH.'/old.jpg');
        Storage::disk('r2')->assertExists('images/'.Formation::THUMBNAIL_STORAGE_PATH.'/'.$formation->thumbnail_path);

        $this->patchJson("/api/v1/admin/formations/{$formation->id}/switch-status", [
            'status' => FormationStatus::EnCours->value,
        ])->assertOk()->assertJsonPath('data.status', 'en_cours');

        $this->patchJson("/api/v1/admin/formations/{$formation->id}/switch-state")
            ->assertOk()->assertJsonPath('data.is_active', false);

        $this->deleteJson("/api/v1/admin/formations/{$formation->id}")->assertOk();
        $this->assertSoftDeleted('formations', ['id' => $formation->id]);
        Storage::disk('r2')->assertExists('images/'.Formation::THUMBNAIL_STORAGE_PATH.'/'.$formation->thumbnail_path);
    }

    public function test_public_list_excludes_inactive_formations_and_inactive_categories(): void
    {
        $active = $this->createFormation();
        $this->createFormation(['libelle' => 'Inactive', 'is_active' => false]);
        $inactiveCategory = $this->createCategory(['libelle' => 'Catégorie inactive', 'is_active' => false]);
        $this->createFormation([
            'formation_category_id' => $inactiveCategory->id,
            'libelle' => 'Catégorie masquée',
        ]);

        $this->getJson('/api/v1/formations')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $active->id);
    }

    public function test_formation_validation_only_enforces_required_date_consistency(): void
    {
        $this->authenticate();
        $category = $this->createCategory();

        $this->postJson('/api/v1/admin/formations', $this->formationPayload($category, [
            'date_debut' => '2026-08-10',
            'date_fin' => '2026-08-09',
        ]))->assertUnprocessable()->assertJsonValidationErrors('date_fin');

        $this->postJson('/api/v1/admin/formations', $this->formationPayload($category, [
            'date_fin_inscription' => '2026-09-01',
        ]))->assertOk();
    }

    public function test_admin_formation_routes_require_authentication(): void
    {
        $this->getJson('/api/v1/admin/formations')->assertUnauthorized();
    }

    public function test_formation_amounts_respect_decimal_precision_and_column_capacity(): void
    {
        $this->authenticate();
        $category = $this->createCategory();

        foreach (['100', '100.5', '100.50'] as $index => $amount) {
            $this->postJson('/api/v1/admin/formations', $this->formationPayload($category, [
                'libelle' => 'Montant valide '.$index,
                'frais_inscription' => $amount,
                'frais_formation' => $amount,
            ]))->assertOk();
        }

        $this->postJson('/api/v1/admin/formations', $this->formationPayload($category, [
            'frais_inscription' => '100.999',
            'frais_formation' => '100.999',
        ]))->assertUnprocessable()
            ->assertJsonValidationErrors(['frais_inscription', 'frais_formation']);

        $this->postJson('/api/v1/admin/formations', $this->formationPayload($category, [
            'frais_inscription' => '10000000000000.00',
            'frais_formation' => '10000000000000.00',
        ]))->assertUnprocessable()
            ->assertJsonValidationErrors(['frais_inscription', 'frais_formation']);

        $formation = $this->createFormation();

        $this->patchJson("/api/v1/admin/formations/{$formation->id}", [
            'frais_inscription' => '100.50',
            'frais_formation' => '100.5',
        ])->assertOk();

        $this->patchJson("/api/v1/admin/formations/{$formation->id}", [
            'frais_inscription' => '100.999',
            'frais_formation' => '10000000000000.00',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['frais_inscription', 'frais_formation']);
    }
}
