<?php

namespace Tests\Feature;

use App\Modules\Administration\Models\User;
use App\Modules\Website\Models\Partner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PartnerManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('r2');
    }

    public function test_public_partner_list_only_returns_active_frontend_fields(): void
    {
        $user = $this->createUser();
        $activePartner = Partner::create([
            'name' => 'Partenaire actif',
            'acronym' => 'PA',
            'domain' => 'Technologie',
            'description' => 'Partenaire visible sur le site.',
            'logo_path' => 'partner.png',
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);
        Partner::create([
            'name' => 'Partenaire désactivé',
            'status' => false,
        ]);

        $this->getJson('/api/v1/partners')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $activePartner->id)
            ->assertJsonPath(
                'data.0.logo_url',
                fn (string $url) => str_contains($url, 'images/partners/partner.png')
            )
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    '*' => ['id', 'name', 'acronym', 'domain', 'description', 'logo_url'],
                ],
            ])
            ->assertJsonMissingPath('data.0.logo_path')
            ->assertJsonMissingPath('data.0.status')
            ->assertJsonMissingPath('data.0.created_by')
            ->assertJsonMissingPath('data.0.updated_by')
            ->assertJsonMissingPath('data.0.created_at')
            ->assertJsonMissingPath('data.0.updated_at');
    }

    public function test_public_partner_detail_handles_active_inactive_and_missing_partners(): void
    {
        $activePartner = Partner::create(['name' => 'Partenaire actif']);
        $inactivePartner = Partner::create([
            'name' => 'Partenaire désactivé',
            'status' => false,
        ]);

        $this->getJson("/api/v1/partners/{$activePartner->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $activePartner->id)
            ->assertJsonMissingPath('data.status')
            ->assertJsonMissingPath('data.logo_path');

        $this->getJson("/api/v1/partners/{$inactivePartner->id}")
            ->assertNotFound()
            ->assertJsonPath('status', 0);

        $this->getJson('/api/v1/partners/999')
            ->assertNotFound()
            ->assertJsonPath('status', 0);

        $this->getJson('/api/v1/partners/not-a-number')->assertNotFound();
    }

    public function test_admin_partner_routes_remain_protected(): void
    {
        $this->getJson('/api/v1/admin/partners')->assertUnauthorized();
        $this->postJson('/api/v1/admin/partners', [])->assertUnauthorized();
        $this->getJson('/api/v1/admin/partners/1')->assertUnauthorized();
        $this->patchJson('/api/v1/admin/partners/1', ['name' => 'Modification'])
            ->assertUnauthorized();
        $this->deleteJson('/api/v1/admin/partners/1')->assertUnauthorized();
        $this->getJson('/api/v1/admin/partners/1/status')->assertUnauthorized();
    }

    public function test_admin_can_read_active_and_inactive_partners(): void
    {
        $user = $this->createUser();
        $activePartner = Partner::create(['name' => 'Partenaire actif']);
        $inactivePartner = Partner::create([
            'name' => 'Partenaire désactivé',
            'status' => false,
        ]);

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/admin/partners')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment([
                'id' => $activePartner->id,
                'status' => true,
            ])
            ->assertJsonFragment([
                'id' => $inactivePartner->id,
                'status' => false,
            ]);

        $this->getJson("/api/v1/admin/partners/{$inactivePartner->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $inactivePartner->id)
            ->assertJsonPath('data.status', false);
    }

    private function createUser(): User
    {
        return User::create([
            'name' => 'Administrateur',
            'telephone' => '620000006',
            'email' => 'partner-admin@example.com',
            'password' => 'password',
        ]);
    }
}
