<?php

namespace Tests\Feature;

use App\Modules\Administration\Models\LogActivity;
use App\Modules\Administration\Models\User;
use App\Modules\Settings\Models\Category;
use App\Modules\Website\Models\Client;
use App\Modules\Website\Models\Project;
use App\Modules\Website\Models\Testimonial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TestimonialManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Client $client;

    protected Project $project;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Administrateur',
            'telephone' => '620000004',
            'email' => 'testimonial-admin@example.com',
            'password' => 'password',
        ]);

        $category = Category::create([
            'libelle' => 'Applications web',
            'created_by' => $this->user->id,
        ]);

        $this->client = Client::create([
            'first_name' => 'Mamadou',
            'last_name' => 'Diallo',
            'job_title' => 'Directeur',
            'created_by' => $this->user->id,
        ]);

        $this->project = Project::create([
            'category_id' => $category->id,
            'title' => 'Portail institutionnel',
            'short_description' => 'Présentation des activités de l’entreprise.',
            'description' => 'Présentation détaillée des activités et des services.',
            'created_by' => $this->user->id,
        ]);
    }

    public function test_admin_testimonial_routes_require_authentication(): void
    {
        $this->getJson('/api/v1/admin/testimonials')->assertUnauthorized();
        $this->postJson('/api/v1/admin/testimonials', $this->testimonial_data())
            ->assertUnauthorized();
        $this->getJson('/api/v1/admin/testimonials/1')->assertUnauthorized();
        $this->patchJson('/api/v1/admin/testimonials/1', ['content' => 'Contenu modifié.'])
            ->assertUnauthorized();
        $this->deleteJson('/api/v1/admin/testimonials/1')->assertUnauthorized();
    }

    public function test_an_authenticated_user_can_create_a_testimonial(): void
    {
        Sanctum::actingAs($this->user);

        $this->postJson('/api/v1/admin/testimonials', $this->testimonial_data())
            ->assertOk()
            ->assertJsonPath('status', 1)
            ->assertJsonPath('data.client.id', $this->client->id)
            ->assertJsonPath('data.project.id', $this->project->id)
            ->assertJsonPath('data.created_by.id', $this->user->id);

        $this->assertDatabaseHas('testimonials', [
            'project_id' => $this->project->id,
            'client_id' => $this->client->id,
            'content' => 'Une excellente collaboration.',
            'created_by' => $this->user->id,
        ]);
        $this->assertDatabaseHas('log_activities', [
            'model' => Testimonial::class,
            'action' => "Création d'un témoignage",
        ]);
    }

    public function test_an_authenticated_user_can_list_and_show_testimonials(): void
    {
        Sanctum::actingAs($this->user);

        Carbon::setTestNow('2026-06-25 10:00:00');
        $olderTestimonial = Testimonial::create($this->testimonial_data() + [
            'created_by' => $this->user->id,
        ]);

        Carbon::setTestNow('2026-06-25 11:00:00');
        $newerTestimonial = Testimonial::create([
            'project_id' => $this->project->id,
            'client_id' => $this->client->id,
            'content' => 'Une seconde expérience réussie.',
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ]);
        Carbon::setTestNow();

        $this->getJson('/api/v1/admin/testimonials')
            ->assertOk()
            ->assertJsonPath('data.0.id', $newerTestimonial->id)
            ->assertJsonPath('data.0.client.id', $this->client->id)
            ->assertJsonPath('data.0.project.id', $this->project->id)
            ->assertJsonPath('data.0.updated_by.id', $this->user->id)
            ->assertJsonPath('data.1.id', $olderTestimonial->id);

        $this->getJson("/api/v1/admin/testimonials/{$olderTestimonial->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $olderTestimonial->id)
            ->assertJsonPath('data.client.first_name', 'Mamadou')
            ->assertJsonPath('data.project.title', 'Portail institutionnel');
    }

    public function test_an_authenticated_user_can_partially_update_a_testimonial(): void
    {
        Sanctum::actingAs($this->user);

        $testimonial = Testimonial::create($this->testimonial_data() + [
            'created_by' => $this->user->id,
        ]);

        $this->patchJson("/api/v1/admin/testimonials/{$testimonial->id}", [
            'content' => 'Une collaboration durable et efficace.',
        ])->assertOk()
            ->assertJsonPath('data.project_id', $this->project->id)
            ->assertJsonPath('data.client_id', $this->client->id)
            ->assertJsonPath('data.content', 'Une collaboration durable et efficace.')
            ->assertJsonPath('data.updated_by.id', $this->user->id);

        $updateLog = LogActivity::where('action', "Modification d'un témoignage")
            ->firstOrFail();

        $this->assertSame(
            'Une excellente collaboration.',
            $updateLog->data['old_value']['content']
        );
        $this->assertSame(
            'Une collaboration durable et efficace.',
            $updateLog->data['new_value']['content']
        );
    }

    public function test_an_authenticated_user_can_delete_a_testimonial(): void
    {
        Sanctum::actingAs($this->user);

        $testimonial = Testimonial::create($this->testimonial_data());

        $this->deleteJson("/api/v1/admin/testimonials/{$testimonial->id}")
            ->assertOk()
            ->assertJsonPath('status', 1);

        $this->assertDatabaseMissing('testimonials', ['id' => $testimonial->id]);
        $this->assertDatabaseHas('log_activities', [
            'model' => Testimonial::class,
            'action' => "Suppression d'un témoignage",
        ]);
    }

    public function test_testimonial_validation_depends_on_the_request_method(): void
    {
        Sanctum::actingAs($this->user);

        $this->postJson('/api/v1/admin/testimonials', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['project_id', 'client_id', 'content']);

        $this->postJson('/api/v1/admin/testimonials', [
            'project_id' => 999,
            'client_id' => 999,
            'content' => 'A',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['project_id', 'client_id', 'content']);

        $testimonial = Testimonial::create($this->testimonial_data());

        $this->patchJson("/api/v1/admin/testimonials/{$testimonial->id}", [
            'content' => 'Contenu partiellement modifié.',
        ])->assertOk();
    }

    public function test_missing_testimonials_return_not_found(): void
    {
        Sanctum::actingAs($this->user);

        $this->getJson('/api/v1/admin/testimonials/999')->assertNotFound();
        $this->patchJson('/api/v1/admin/testimonials/999', ['content' => 'Introuvable'])
            ->assertNotFound();
        $this->deleteJson('/api/v1/admin/testimonials/999')->assertNotFound();
    }

    public function test_public_testimonials_only_expose_frontend_fields(): void
    {
        $testimonial = Testimonial::create($this->testimonial_data() + [
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ]);

        $this->getJson('/api/v1/testimonials')
            ->assertOk()
            ->assertJsonPath('data.0.id', $testimonial->id)
            ->assertJsonPath('data.0.client.job_title', 'Directeur')
            ->assertJsonPath('data.0.project.short_description', 'Présentation des activités de l’entreprise.')
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'content',
                        'client' => ['id', 'first_name', 'last_name', 'job_title'],
                        'project' => ['id', 'title', 'short_description'],
                    ],
                ],
            ])
            ->assertJsonMissingPath('data.0.project_id')
            ->assertJsonMissingPath('data.0.client_id')
            ->assertJsonMissingPath('data.0.created_by')
            ->assertJsonMissingPath('data.0.updated_by')
            ->assertJsonMissingPath('data.0.created_at')
            ->assertJsonMissingPath('data.0.updated_at');
    }

    public function test_deleting_a_project_also_deletes_its_testimonials(): void
    {
        Sanctum::actingAs($this->user);

        $testimonial = Testimonial::create($this->testimonial_data());

        $this->deleteJson("/api/v1/admin/projects/{$this->project->id}")
            ->assertOk();

        $this->assertDatabaseMissing('testimonials', ['id' => $testimonial->id]);
    }

    private function testimonial_data(): array
    {
        return [
            'project_id' => $this->project->id,
            'client_id' => $this->client->id,
            'content' => 'Une excellente collaboration.',
        ];
    }
}
