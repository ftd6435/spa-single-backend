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
            ->assertJsonPath('data.category.id', $category->id)
            ->assertJsonPath('data.created_by.id', $this->user->id)
            ->assertJsonMissingPath('data.category.created_by')
            ->assertJsonMissingPath('data.category.updated_by');

        $formation = Formation::findOrFail($response->json('data.id'));
        Storage::disk('r2')->assertExists('images/'.Formation::THUMBNAIL_STORAGE_PATH.'/'.$formation->thumbnail_path);

        $this->getJson("/api/v1/formations/{$formation->id}")
            ->assertOk()
            ->assertJsonPath('data.formation_category_id', $category->id)
            ->assertJsonPath('data.status', 'en_attente')
            ->assertJsonPath('data.is_active', true)
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

    public function test_public_reads_include_inactive_items_but_exclude_soft_deleted_items(): void
    {
        $active = $this->createFormation();
        $inactive = $this->createFormation(['libelle' => 'Inactive', 'is_active' => false]);
        $inactiveCategory = $this->createCategory(['libelle' => 'Catégorie inactive', 'is_active' => false]);
        $inInactiveCategory = $this->createFormation([
            'formation_category_id' => $inactiveCategory->id,
            'libelle' => 'Catégorie masquée',
        ]);
        $deletedCategory = $this->createCategory(['libelle' => 'Catégorie supprimée']);
        $hiddenByDeletedCategory = $this->createFormation([
            'formation_category_id' => $deletedCategory->id,
            'libelle' => 'Catégorie supprimée',
        ]);
        $softDeleted = $this->createFormation(['libelle' => 'Formation supprimée']);
        $deletedCategory->delete();
        $softDeleted->delete();

        $response = $this->getJson('/api/v1/formations')
            ->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonFragment(['id' => $inactive->id, 'is_active' => false])
            ->assertJsonFragment(['id' => $inactiveCategory->id, 'is_active' => false]);

        $this->assertEqualsCanonicalizing(
            [$active->id, $inactive->id, $inInactiveCategory->id],
            collect($response->json('data'))->pluck('id')->all()
        );

        $this->getJson("/api/v1/formations/{$inactive->id}")
            ->assertOk()
            ->assertJsonPath('data.is_active', false);
        $this->getJson("/api/v1/formations/{$hiddenByDeletedCategory->id}")->assertNotFound();
        $this->getJson("/api/v1/formations/{$softDeleted->id}")->assertNotFound();
    }

    public function test_public_list_supports_category_status_and_active_state_filters(): void
    {
        $firstCategory = $this->createCategory();
        $secondCategory = $this->createCategory(['libelle' => 'Gestion']);
        $waiting = $this->createFormation(['formation_category_id' => $firstCategory->id]);
        $running = $this->createFormation([
            'formation_category_id' => $secondCategory->id,
            'libelle' => 'Formation en cours',
            'status' => FormationStatus::EnCours,
            'is_active' => false,
        ]);

        $this->getJson('/api/v1/formations?formation_category_id='.$firstCategory->id)
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $waiting->id);

        $this->getJson('/api/v1/formations?status='.FormationStatus::EnCours->value)
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $running->id);

        $this->getJson('/api/v1/formations?is_active=0')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $running->id);
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
        $category = $this->createCategory();
        $formation = $this->createFormation();

        $this->getJson('/api/v1/admin/formations')->assertStatus(405);
        $this->getJson("/api/v1/admin/formations/{$formation->id}")->assertStatus(405);
        $this->postJson('/api/v1/admin/formations', $this->formationPayload($category))->assertUnauthorized();
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
