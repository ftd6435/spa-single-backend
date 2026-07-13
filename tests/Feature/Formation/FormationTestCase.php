<?php

namespace Tests\Feature\Formation;

use App\Modules\Administration\Models\User;
use App\Modules\Formation\Enums\FormationStatus;
use App\Modules\Formation\Models\Formation;
use App\Modules\Formation\Models\FormationCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

abstract class FormationTestCase extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('r2');

        $this->user = User::create([
            'name' => 'Administrateur Formation',
            'telephone' => '620100000',
            'email' => 'formation-admin@example.com',
            'password' => 'password',
        ]);
    }

    protected function authenticate(): void
    {
        Sanctum::actingAs($this->user);
    }

    protected function createCategory(array $attributes = []): FormationCategory
    {
        return FormationCategory::create(array_merge([
            'libelle' => 'Développement web',
            'description' => 'Formations techniques',
            'created_by' => $this->user->id,
        ], $attributes));
    }

    protected function createFormation(array $attributes = []): Formation
    {
        $categoryId = $attributes['formation_category_id']
            ?? FormationCategory::first()?->id
            ?? $this->createCategory()->id;

        return Formation::create(array_merge([
            'formation_category_id' => $categoryId,
            'libelle' => 'Laravel avancé',
            'short_description' => 'Créer des API robustes',
            'description' => '<p>Formation complète</p>',
            'date_debut' => now()->addMonth()->toDateString(),
            'date_fin' => now()->addMonth()->addDays(2)->toDateString(),
            'nombre_places' => 20,
            'lieu_formation' => 'Conakry',
            'date_fin_inscription' => now()->addWeeks(2)->toDateString(),
            'frais_inscription' => 100000,
            'frais_formation' => 500000,
            'status' => FormationStatus::EnAttente,
            'created_by' => $this->user->id,
        ], $attributes));
    }

    protected function formationPayload(FormationCategory $category, array $overrides = []): array
    {
        return array_merge([
            'formation_category_id' => $category->id,
            'libelle' => 'API Laravel',
            'short_description' => 'Conception API',
            'description' => '<p>Contenu de la formation</p>',
            'date_debut' => '2026-08-10',
            'date_fin' => '2026-08-12',
            'nombre_places' => 15,
            'lieu_formation' => 'Conakry',
            'date_fin_inscription' => '2026-08-15',
            'frais_inscription' => 100000,
            'frais_formation' => 450000,
        ], $overrides);
    }
}
