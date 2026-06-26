<?php

namespace Tests\Feature;

use App\Modules\Administration\Models\LogActivity;
use App\Modules\Administration\Models\User;
use App\Modules\Settings\Models\Category;
use App\Modules\Website\Models\Project;
use App\Modules\Website\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProjectManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Category $category;

    protected Service $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Administrateur',
            'telephone' => '620000002',
            'email' => 'project-admin@example.com',
            'password' => 'password',
        ]);

        $this->category = Category::create([
            'libelle' => 'Applications web',
            'description' => 'Projets web',
            'created_by' => $this->user->id,
        ]);

        $this->service = Service::create([
            'title' => 'Développement web',
            'short_description' => 'Applications web sur mesure',
            'description' => 'Conception et développement d’applications web.',
            'created_by' => $this->user->id,
        ]);
    }

    public function test_admin_project_routes_require_authentication(): void
    {
        $this->getJson('/api/v1/admin/projects')->assertUnauthorized();

        $this->postJson('/api/v1/admin/projects', $this->projectData())
            ->assertUnauthorized();
    }

    public function test_an_authenticated_user_can_create_and_partially_update_a_project(): void
    {
        Sanctum::actingAs($this->user);

        $createResponse = $this->postJson('/api/v1/admin/projects', $this->projectData());

        $createResponse->assertOk()
            ->assertJsonPath('status', 1)
            ->assertJsonPath('data.category.id', $this->category->id)
            ->assertJsonPath('data.service.id', $this->service->id)
            ->assertJsonPath('data.created_by.id', $this->user->id);

        $project = Project::firstOrFail();

        $this->patchJson("/api/v1/admin/projects/{$project->id}", [
            'title' => 'Portail SPA actualisé',
        ])->assertOk()
            ->assertJsonPath('data.title', 'Portail SPA actualisé')
            ->assertJsonPath('data.service.id', $this->service->id)
            ->assertJsonPath('data.updated_by.id', $this->user->id);

        $this->assertSame(2, LogActivity::where('model', Project::class)->count());
    }

    public function test_service_can_be_omitted_or_removed_during_project_management(): void
    {
        Sanctum::actingAs($this->user);

        $project = Project::create($this->projectData() + [
            'created_by' => $this->user->id,
        ]);

        $this->patchJson("/api/v1/admin/projects/{$project->id}", [
            'service_id' => null,
        ])->assertOk()
            ->assertJsonPath('data.service_id', null)
            ->assertJsonPath('data.service', null);

        $this->postJson('/api/v1/admin/projects', array_merge(
            $this->projectData(),
            ['service_id' => null]
        ))->assertOk()
            ->assertJsonPath('data.service_id', null);
    }

    public function test_project_validation_rejects_invalid_relations_and_demo_link(): void
    {
        Sanctum::actingAs($this->user);

        $this->postJson('/api/v1/admin/projects', [
            'category_id' => 999,
            'service_id' => 999,
            'title' => 'Projet',
            'short_description' => 'Description courte',
            'description' => 'Description complète',
            'demo_link' => 'lien-invalide',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['category_id', 'service_id', 'demo_link']);
    }

    public function test_public_projects_can_be_filtered_without_exposing_audit_fields(): void
    {
        $otherCategory = Category::create([
            'libelle' => 'Applications mobiles',
            'created_by' => $this->user->id,
        ]);
        $otherService = Service::create([
            'title' => 'Développement mobile',
            'short_description' => 'Applications mobiles sur mesure',
            'description' => 'Conception et développement d’applications mobiles.',
            'created_by' => $this->user->id,
        ]);

        $matchingProject = Project::create($this->projectData() + [
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ]);

        Project::create(array_merge($this->projectData(), [
            'category_id' => $otherCategory->id,
            'title' => 'Autre projet',
            'created_by' => $this->user->id,
        ]));
        Project::create(array_merge($this->projectData(), [
            'service_id' => $otherService->id,
            'title' => 'Projet mobile',
            'created_by' => $this->user->id,
        ]));

        $this->getJson("/api/v1/projects?category_id={$this->category->id}")
            ->assertOk()
            ->assertJsonCount(2, 'data');

        $this->getJson("/api/v1/projects?service_id={$this->service->id}")
            ->assertOk()
            ->assertJsonCount(2, 'data');

        $this->getJson(
            "/api/v1/projects?category_id={$this->category->id}&service_id={$this->service->id}"
        )->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $matchingProject->id)
            ->assertJsonMissingPath('data.0.created_by')
            ->assertJsonMissingPath('data.0.updated_by')
            ->assertJsonMissingPath('data.0.created_at')
            ->assertJsonMissingPath('data.0.category.created_by')
            ->assertJsonMissingPath('data.0.service.created_by');
    }

    public function test_public_project_detail_and_missing_projects_are_handled(): void
    {
        $project = Project::create($this->projectData());

        $this->getJson("/api/v1/projects/{$project->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $project->id)
            ->assertJsonPath('data.demo_link', 'https://example.com/demo')
            ->assertJsonPath('data.category.id', $this->category->id)
            ->assertJsonPath('data.service.id', $this->service->id)
            ->assertJsonMissingPath('data.created_by');

        $this->getJson('/api/v1/projects/999')->assertNotFound();
    }

    public function test_an_authenticated_user_can_delete_a_project(): void
    {
        Sanctum::actingAs($this->user);

        $project = Project::create($this->projectData());

        $this->deleteJson("/api/v1/admin/projects/{$project->id}")
            ->assertOk()
            ->assertJsonPath('status', 1);

        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
        $this->assertDatabaseHas('log_activities', [
            'model' => Project::class,
            'action' => "Suppression d'un projet",
        ]);
    }

    private function projectData(): array
    {
        return [
            'category_id' => $this->category->id,
            'service_id' => $this->service->id,
            'title' => 'Portail SPA Technology',
            'short_description' => 'Présentation des activités de SPA Technology.',
            'description' => 'Un portail institutionnel présentant les services et réalisations.',
            'demo_link' => 'https://example.com/demo',
        ];
    }
}
