<?php

namespace Tests\Feature;

use App\Modules\Administration\Models\User;
use App\Modules\Settings\Models\Tag;
use App\Modules\Website\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ServiceManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('r2');

        $this->user = User::create([
            'name' => 'Administrateur',
            'telephone' => '620000000',
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);
    }

    public function test_admin_service_routes_require_authentication(): void
    {
        $this->getJson('/api/v1/admin/services')->assertUnauthorized();
        $this->postJson('/api/v1/admin/services', [])->assertUnauthorized();
        $this->getJson('/api/v1/admin/services/1')->assertUnauthorized();
        $this->patchJson('/api/v1/admin/services/1', ['title' => 'Modification'])
            ->assertUnauthorized();
        $this->deleteJson('/api/v1/admin/services/1')->assertUnauthorized();
        $this->getJson('/api/v1/admin/services/1/status')->assertUnauthorized();
    }

    public function test_public_service_list_only_returns_active_frontend_fields_without_tags(): void
    {
        $tag = Tag::create([
            'libelle' => 'Laravel',
            'created_by' => $this->user->id,
        ]);
        $activeService = Service::create([
            'title' => 'Développement backend',
            'short_description' => 'API Laravel maintenable',
            'description' => 'Conception et développement d’une API Laravel.',
            'icon' => 'code',
            'benefits' => ['Code maintenable', 'API sécurisée'],
            'image_path' => 'service.jpg',
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ]);
        $activeService->tags()->attach($tag->id, ['created_by' => $this->user->id]);
        Service::create([
            'title' => 'Service désactivé',
            'short_description' => 'Service non visible',
            'description' => 'Ce service ne doit pas être exposé.',
            'status' => false,
        ]);

        $this->getJson('/api/v1/services')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $activeService->id)
            ->assertJsonPath(
                'data.0.image_url',
                fn (string $url) => str_contains($url, 'images/services/service.jpg')
            )
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'icon',
                        'image_url',
                        'title',
                        'short_description',
                        'description',
                        'benefits',
                    ],
                ],
            ])
            ->assertJsonMissingPath('data.0.image_path')
            ->assertJsonMissingPath('data.0.tags')
            ->assertJsonMissingPath('data.0.status')
            ->assertJsonMissingPath('data.0.created_by')
            ->assertJsonMissingPath('data.0.updated_by')
            ->assertJsonMissingPath('data.0.created_at')
            ->assertJsonMissingPath('data.0.updated_at');
    }

    public function test_public_service_detail_handles_active_inactive_and_missing_services(): void
    {
        $activeService = Service::create([
            'title' => 'Service actif',
            'short_description' => 'Service visible',
            'description' => 'Ce service est visible publiquement.',
        ]);
        $inactiveService = Service::create([
            'title' => 'Service désactivé',
            'short_description' => 'Service non visible',
            'description' => 'Ce service ne doit pas être exposé.',
            'status' => false,
        ]);

        $this->getJson("/api/v1/services/{$activeService->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $activeService->id)
            ->assertJsonMissingPath('data.status')
            ->assertJsonMissingPath('data.tags')
            ->assertJsonMissingPath('data.image_path');

        $this->getJson("/api/v1/services/{$inactiveService->id}")
            ->assertNotFound()
            ->assertJsonPath('status', 0);

        $this->getJson('/api/v1/services/999')
            ->assertNotFound()
            ->assertJsonPath('status', 0);

        $this->getJson('/api/v1/services/not-a-number')->assertNotFound();
    }

    public function test_admin_can_read_active_and_inactive_services_with_tags(): void
    {
        $tag = Tag::create(['libelle' => 'Laravel']);
        $activeService = Service::create([
            'title' => 'Service actif',
            'short_description' => 'Service visible',
            'description' => 'Ce service est actif.',
        ]);
        $inactiveService = Service::create([
            'title' => 'Service désactivé',
            'short_description' => 'Service non visible',
            'description' => 'Ce service reste administrable.',
            'status' => false,
        ]);
        $inactiveService->tags()->attach($tag->id);

        Sanctum::actingAs($this->user);

        $this->getJson('/api/v1/admin/services')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment([
                'id' => $activeService->id,
                'status' => true,
            ])
            ->assertJsonFragment([
                'id' => $inactiveService->id,
                'status' => false,
            ]);

        $this->getJson("/api/v1/admin/services/{$inactiveService->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $inactiveService->id)
            ->assertJsonPath('data.status', false)
            ->assertJsonCount(1, 'data.tags');
    }

    public function test_an_authenticated_user_can_create_a_service_with_an_image_and_tags(): void
    {
        Sanctum::actingAs($this->user);

        $tags = collect([
            Tag::create(['libelle' => 'Laravel', 'created_by' => $this->user->id]),
            Tag::create(['libelle' => 'API', 'created_by' => $this->user->id]),
        ]);

        $response = $this->post('/api/v1/admin/services', [
            'title' => 'Développement backend',
            'short_description' => 'API Laravel maintenable',
            'description' => 'Conception et développement d’une API Laravel.',
            'icon' => 'code',
            'benefits' => ['Code maintenable', 'API sécurisée'],
            'tag_ids' => $tags->pluck('id')->all(),
            'image' => UploadedFile::fake()->create('service.jpg', 100, 'image/jpeg'),
        ], ['Accept' => 'application/json']);

        $response->assertOk()
            ->assertJsonPath('status', 1)
            ->assertJsonPath('data.created_by.id', $this->user->id)
            ->assertJsonCount(2, 'data.tags');

        $service = Service::firstOrFail();

        $this->assertSame(['Code maintenable', 'API sécurisée'], $service->benefits);
        $this->assertEqualsCanonicalizing($tags->pluck('id')->all(), $service->tags()->pluck('tags.id')->all());
        Storage::disk('r2')->assertExists('images/services/'.$service->image_path);
    }

    public function test_a_service_can_be_partially_updated_and_its_tags_synchronized(): void
    {
        Sanctum::actingAs($this->user);

        $firstTag = Tag::create(['libelle' => 'Initial', 'created_by' => $this->user->id]);
        $secondTag = Tag::create(['libelle' => 'Final', 'created_by' => $this->user->id]);
        Storage::disk('r2')->put('images/services/old-service.jpg', 'old image');

        $service = Service::create([
            'title' => 'Ancien titre',
            'short_description' => 'Description courte',
            'description' => 'Description complète',
            'image_path' => 'old-service.jpg',
            'created_by' => $this->user->id,
        ]);
        $service->tags()->attach($firstTag->id, ['created_by' => $this->user->id]);

        $response = $this->post("/api/v1/admin/services/{$service->id}", [
            '_method' => 'PATCH',
            'title' => 'Nouveau titre',
            'benefits' => ['Livraison rapide'],
            'tag_ids' => [$secondTag->id],
            'image' => UploadedFile::fake()->create('new-service.jpg', 100, 'image/jpeg'),
        ], ['Accept' => 'application/json']);

        $response->assertOk()
            ->assertJsonPath('data.title', 'Nouveau titre')
            ->assertJsonCount(1, 'data.tags');

        $service->refresh();

        $this->assertSame($this->user->id, $service->updated_by);
        $this->assertSame(['Livraison rapide'], $service->benefits);
        $this->assertTrue($service->tags()->whereKey($secondTag->id)->exists());
        $this->assertFalse($service->tags()->whereKey($firstTag->id)->exists());
        Storage::disk('r2')->assertMissing('images/services/old-service.jpg');
        Storage::disk('r2')->assertExists('images/services/'.$service->image_path);
    }

    public function test_tags_can_be_added_and_removed_without_duplicates(): void
    {
        Sanctum::actingAs($this->user);

        $tag = Tag::create(['libelle' => 'Architecture', 'created_by' => $this->user->id]);
        $service = Service::create([
            'title' => 'Audit',
            'short_description' => 'Audit technique',
            'description' => 'Audit détaillé du backend.',
            'created_by' => $this->user->id,
        ]);

        $this->patchJson("/api/v1/admin/services/{$service->id}", [
            'tag_ids' => [$tag->id],
        ])->assertOk()
            ->assertJsonPath('status', 1)
            ->assertJsonCount(1, 'data.tags');

        $this->assertDatabaseCount('service_tag', 1);
        $this->assertTrue($service->fresh()->tags()->whereKey($tag->id)->exists());

        $this->patchJson("/api/v1/admin/services/{$service->id}", [
            'tag_ids' => [$tag->id],
        ])->assertOk()
            ->assertJsonPath('status', 1)
            ->assertJsonCount(1, 'data.tags');

        $this->assertDatabaseCount('service_tag', 1);

        $this->patchJson("/api/v1/admin/services/{$service->id}", [
            'tag_ids' => [],
        ])
            ->assertOk()
            ->assertJsonPath('status', 1)
            ->assertJsonCount(0, 'data.tags');

        $this->assertDatabaseCount('service_tag', 0);
    }

    public function test_deleting_a_service_also_deletes_its_image(): void
    {
        Sanctum::actingAs($this->user);

        Storage::disk('r2')->put('images/services/service.jpg', 'image');

        $service = Service::create([
            'title' => 'Service supprimable',
            'short_description' => 'Description courte',
            'description' => 'Description complète',
            'image_path' => 'service.jpg',
            'created_by' => $this->user->id,
        ]);

        $this->deleteJson("/api/v1/admin/services/{$service->id}")
            ->assertOk()
            ->assertJsonPath('status', 1);

        $this->assertDatabaseMissing('services', ['id' => $service->id]);
        Storage::disk('r2')->assertMissing('images/services/service.jpg');
    }
}
