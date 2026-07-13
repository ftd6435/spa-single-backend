<?php

namespace Tests\Feature\Formation;

use App\Modules\Formation\Models\FormationCategory;

class FormationCategoryFeatureTest extends FormationTestCase
{
    public function test_admin_category_routes_require_authentication(): void
    {
        $category = $this->createCategory();

        $this->getJson('/api/v1/admin/formation-categories')->assertStatus(405);
        $this->getJson("/api/v1/admin/formation-categories/{$category->id}")->assertStatus(405);
        $this->postJson('/api/v1/admin/formation-categories', [
            'libelle' => 'Backend',
        ])->assertUnauthorized();
    }

    public function test_admin_can_manage_categories_and_public_reads_include_inactive_ones(): void
    {
        $this->authenticate();

        $response = $this->postJson('/api/v1/admin/formation-categories', [
            'libelle' => 'Backend',
            'description' => 'Formations backend',
        ])->assertOk()
            ->assertJsonPath('data.libelle', 'Backend')
            ->assertJsonPath('data.created_by.id', $this->user->id);

        $category = FormationCategory::findOrFail($response->json('data.id'));

        $this->patchJson("/api/v1/admin/formation-categories/{$category->id}", [
            'description' => 'Description actualisée',
            'is_active' => false,
        ])->assertOk()
            ->assertJsonPath('data.is_active', false);

        $this->getJson('/api/v1/formation-categories')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $category->id)
            ->assertJsonPath('data.0.is_active', false);

        $this->getJson("/api/v1/formation-categories/{$category->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $category->id)
            ->assertJsonPath('data.is_active', false);

        $this->deleteJson("/api/v1/admin/formation-categories/{$category->id}")
            ->assertOk();

        $this->assertSoftDeleted('formation_categories', ['id' => $category->id]);
        $this->getJson('/api/v1/formation-categories')->assertOk()->assertJsonCount(0, 'data');
        $this->getJson("/api/v1/formation-categories/{$category->id}")->assertNotFound();
        $this->assertDatabaseHas('log_activities', [
            'model' => FormationCategory::class,
        ]);
    }

    public function test_category_validation_rejects_duplicate_labels(): void
    {
        $this->authenticate();
        $this->createCategory(['libelle' => 'Backend']);

        $this->postJson('/api/v1/admin/formation-categories', [
            'libelle' => 'Backend',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('libelle');
    }
}
