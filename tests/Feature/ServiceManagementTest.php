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
        Sanctum::actingAs($this->user);
    }

    public function test_an_authenticated_user_can_create_a_service_with_an_image_and_tags(): void
    {
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
