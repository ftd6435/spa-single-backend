<?php

namespace Tests\Feature;

use App\Modules\Administration\Models\LogActivity;
use App\Modules\Administration\Models\User;
use App\Modules\Website\Models\Vision;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class VisionManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Administrateur',
            'telephone' => '620000001',
            'email' => 'vision-admin@example.com',
            'password' => 'password',
        ]);
    }

    public function test_an_authenticated_user_can_manage_a_vision(): void
    {
        Sanctum::actingAs($this->user);

        $createResponse = $this->postJson('/api/v1/admin/visions', [
            'title' => 'Notre vision',
            'description' => 'Construire des solutions numériques durables.',
            'author' => 'SPA Technology',
        ]);

        $createResponse->assertOk()
            ->assertJsonPath('status', 1)
            ->assertJsonPath('data.created_by.id', $this->user->id);

        $vision = Vision::firstOrFail();

        $this->patchJson("/api/v1/admin/visions/{$vision->id}", [
            'title' => 'Notre vision actualisée',
        ])->assertOk()
            ->assertJsonPath('data.title', 'Notre vision actualisée')
            ->assertJsonPath('data.description', 'Construire des solutions numériques durables.')
            ->assertJsonPath('data.updated_by.id', $this->user->id);

        $this->getJson("/api/v1/admin/visions/{$vision->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $vision->id);

        $this->deleteJson("/api/v1/admin/visions/{$vision->id}")
            ->assertOk()
            ->assertJsonPath('status', 1);

        $this->assertDatabaseMissing('visions', ['id' => $vision->id]);
        $this->assertSame(3, LogActivity::where('model', Vision::class)->count());

        $updateLog = LogActivity::where('action', "Modification d'une vision")->firstOrFail();
        $this->assertSame('Notre vision', $updateLog->data['old_value']['title']);
        $this->assertSame('Notre vision actualisée', $updateLog->data['new_value']['title']);
    }

    public function test_vision_validation_depends_on_the_request_method(): void
    {
        Sanctum::actingAs($this->user);

        $this->postJson('/api/v1/admin/visions', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['title', 'description']);

        $vision = Vision::create([
            'title' => 'Vision initiale',
            'description' => 'Description initiale',
            'created_by' => $this->user->id,
        ]);

        $this->patchJson("/api/v1/admin/visions/{$vision->id}", [
            'author' => 'Nouvel auteur',
        ])->assertOk()
            ->assertJsonPath('data.author', 'Nouvel auteur');
    }

    public function test_missing_visions_return_not_found(): void
    {
        Sanctum::actingAs($this->user);

        $this->getJson('/api/v1/admin/visions/999')->assertNotFound();
        $this->patchJson('/api/v1/admin/visions/999', ['title' => 'Introuvable'])->assertNotFound();
        $this->deleteJson('/api/v1/admin/visions/999')->assertNotFound();
    }

    public function test_admin_vision_routes_require_authentication(): void
    {
        $this->getJson('/api/v1/admin/visions')->assertUnauthorized();
        $this->postJson('/api/v1/admin/visions', [
            'title' => 'Vision privée',
            'description' => 'Description privée',
        ])->assertUnauthorized();
    }

    public function test_public_visions_only_expose_frontend_fields(): void
    {
        $vision = Vision::create([
            'title' => 'Vision publique',
            'description' => 'Description publique',
            'author' => 'SPA Technology',
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ]);
        Vision::create([
            'title' => 'Vision masquée',
            'description' => 'Cette vision ne doit pas être exposée.',
            'status' => false,
        ]);

        $this->getJson('/api/v1/visions')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $vision->id)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    '*' => ['id', 'title', 'description', 'author'],
                ],
            ])
            ->assertJsonMissingPath('data.0.status')
            ->assertJsonMissingPath('data.0.created_by')
            ->assertJsonMissingPath('data.0.updated_by')
            ->assertJsonMissingPath('data.0.createdBy')
            ->assertJsonMissingPath('data.0.updatedBy')
            ->assertJsonMissingPath('data.0.created_at')
            ->assertJsonMissingPath('data.0.updated_at');
    }
}
