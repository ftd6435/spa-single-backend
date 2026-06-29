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

class ClientManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Administrateur',
            'telephone' => '620000003',
            'email' => 'client-admin@example.com',
            'password' => 'password',
        ]);
    }

    public function test_admin_client_routes_require_authentication(): void
    {
        $this->getJson('/api/v1/admin/clients')->assertUnauthorized();
        $this->postJson('/api/v1/admin/clients', $this->clientData())->assertUnauthorized();
        $this->getJson('/api/v1/admin/clients/1')->assertUnauthorized();
        $this->patchJson('/api/v1/admin/clients/1', ['first_name' => 'Mamadou'])
            ->assertUnauthorized();
        $this->deleteJson('/api/v1/admin/clients/1')->assertUnauthorized();
    }

    public function test_an_authenticated_user_can_create_a_client(): void
    {
        Sanctum::actingAs($this->user);

        $this->postJson('/api/v1/admin/clients', $this->clientData())
            ->assertOk()
            ->assertJsonPath('status', 1)
            ->assertJsonPath('data.first_name', 'Mamadou')
            ->assertJsonPath('data.created_by.id', $this->user->id);

        $this->assertDatabaseHas('clients', [
            'first_name' => 'Mamadou',
            'last_name' => 'Diallo',
            'created_by' => $this->user->id,
        ]);
        $this->assertDatabaseHas('log_activities', [
            'model' => Client::class,
            'action' => "Création d'un client",
        ]);
    }

    public function test_an_authenticated_user_can_list_clients_from_newest_to_oldest(): void
    {
        Sanctum::actingAs($this->user);

        Carbon::setTestNow('2026-06-25 10:00:00');
        $olderClient = Client::create($this->clientData() + [
            'created_by' => $this->user->id,
        ]);

        Carbon::setTestNow('2026-06-25 11:00:00');
        $newerClient = Client::create([
            'first_name' => 'Aïssatou',
            'last_name' => 'Camara',
            'job_title' => 'Directrice',
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ]);
        Carbon::setTestNow();

        $this->getJson('/api/v1/admin/clients')
            ->assertOk()
            ->assertJsonPath('data.0.id', $newerClient->id)
            ->assertJsonPath('data.0.updated_by.id', $this->user->id)
            ->assertJsonPath('data.1.id', $olderClient->id)
            ->assertJsonPath('data.1.created_by.id', $this->user->id);
    }

    public function test_an_authenticated_user_can_show_a_client(): void
    {
        Sanctum::actingAs($this->user);

        $client = Client::create($this->clientData() + [
            'created_by' => $this->user->id,
        ]);

        $this->getJson("/api/v1/admin/clients/{$client->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $client->id)
            ->assertJsonPath('data.job_title', 'Responsable commercial')
            ->assertJsonMissingPath('data.testimonials');
    }

    public function test_an_authenticated_user_can_partially_update_a_client(): void
    {
        Sanctum::actingAs($this->user);

        $client = Client::create($this->clientData() + [
            'created_by' => $this->user->id,
        ]);

        $this->patchJson("/api/v1/admin/clients/{$client->id}", [
            'job_title' => 'Directeur commercial',
        ])->assertOk()
            ->assertJsonPath('data.first_name', 'Mamadou')
            ->assertJsonPath('data.last_name', 'Diallo')
            ->assertJsonPath('data.job_title', 'Directeur commercial')
            ->assertJsonPath('data.updated_by.id', $this->user->id);

        $updateLog = LogActivity::where('action', "Modification d'un client")->firstOrFail();

        $this->assertSame('Responsable commercial', $updateLog->data['old_value']['job_title']);
        $this->assertSame('Directeur commercial', $updateLog->data['new_value']['job_title']);
    }

    public function test_deleting_a_client_also_deletes_its_testimonials(): void
    {
        Sanctum::actingAs($this->user);

        $client = Client::create($this->clientData() + [
            'created_by' => $this->user->id,
        ]);
        $category = Category::create([
            'libelle' => 'Applications web',
            'created_by' => $this->user->id,
        ]);
        $project = Project::create([
            'category_id' => $category->id,
            'title' => 'Portail institutionnel',
            'short_description' => 'Présentation de l’entreprise.',
            'description' => 'Présentation détaillée de l’entreprise et de ses services.',
            'created_by' => $this->user->id,
        ]);
        $testimonial = Testimonial::create([
            'project_id' => $project->id,
            'client_id' => $client->id,
            'content' => 'Une excellente collaboration.',
            'created_by' => $this->user->id,
        ]);

        $this->deleteJson("/api/v1/admin/clients/{$client->id}")
            ->assertOk()
            ->assertJsonPath('status', 1);

        $this->assertDatabaseMissing('clients', ['id' => $client->id]);
        $this->assertDatabaseMissing('testimonials', ['id' => $testimonial->id]);
        $this->assertDatabaseHas('log_activities', [
            'model' => Client::class,
            'action' => "Suppression d'un client",
        ]);
    }

    public function test_client_validation_depends_on_the_request_method(): void
    {
        Sanctum::actingAs($this->user);

        $this->postJson('/api/v1/admin/clients', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['first_name', 'last_name']);

        $this->postJson('/api/v1/admin/clients', [
            'first_name' => 'M',
            'last_name' => str_repeat('D', 161),
            'job_title' => str_repeat('J', 161),
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['first_name', 'last_name', 'job_title']);

        $client = Client::create($this->clientData() + [
            'created_by' => $this->user->id,
        ]);

        $this->patchJson("/api/v1/admin/clients/{$client->id}", [
            'job_title' => null,
        ])->assertOk()
            ->assertJsonPath('data.job_title', null);
    }

    public function test_missing_clients_return_not_found(): void
    {
        Sanctum::actingAs($this->user);

        $this->getJson('/api/v1/admin/clients/999')->assertNotFound();
        $this->patchJson('/api/v1/admin/clients/999', ['first_name' => 'Mamadou'])
            ->assertNotFound();
        $this->deleteJson('/api/v1/admin/clients/999')->assertNotFound();
    }

    private function clientData(): array
    {
        return [
            'first_name' => 'Mamadou',
            'last_name' => 'Diallo',
            'job_title' => 'Responsable commercial',
        ];
    }
}
