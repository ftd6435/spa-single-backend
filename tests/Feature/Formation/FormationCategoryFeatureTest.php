<?php

namespace Tests\Feature\Formation;

use App\Modules\Formation\Models\FormationCategory;

class FormationCategoryFeatureTest extends FormationTestCase
{
    public function test_admin_category_routes_require_authentication(): void
    {
        $this->getJson('/api/v1/admin/formation-categories')->assertUnauthorized();
        $this->postJson('/api/v1/admin/formation-categories', [
            'libelle' => 'Backend',
        ])->assertUnauthorized();
    }

    public function test_admin_can_manage_categories_and_public_only_sees_active_ones(): void
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
            ->assertJsonCount(0, 'data');

        $this->deleteJson("/api/v1/admin/formation-categories/{$category->id}")
            ->assertOk();

        $this->assertSoftDeleted('formation_categories', ['id' => $category->id]);
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
