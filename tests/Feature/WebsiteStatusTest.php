<?php

namespace Tests\Feature;

use App\Modules\Administration\Models\LogActivity;
use App\Modules\Administration\Models\User;
use App\Modules\Settings\Models\Category;
use App\Modules\Website\Models\Client;
use App\Modules\Website\Models\Partner;
use App\Modules\Website\Models\Project;
use App\Modules\Website\Models\Service;
use App\Modules\Website\Models\Statistic;
use App\Modules\Website\Models\Testimonial;
use App\Modules\Website\Models\Vision;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class WebsiteStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_switch_status_for_every_website_resource(): void
    {
        $user = $this->createUser();
        $resources = $this->createWebsiteResources($user);

        foreach ($resources as $resource => $model) {
            $this->getJson("/api/v1/admin/{$resource}/{$model->id}/status")
                ->assertUnauthorized();
        }

        Sanctum::actingAs($user);

        foreach ($resources as $resource => $model) {
            $this->getJson("/api/v1/admin/{$resource}/{$model->id}")
                ->assertOk()
                ->assertJsonPath('data.status', true);

            $this->getJson("/api/v1/admin/{$resource}/{$model->id}/status")
                ->assertOk()
                ->assertJsonPath('status', 1);
            $this->getJson("/api/v1/admin/{$resource}/999/status")
                ->assertNotFound();

            $model->refresh();

            $this->assertFalse($model->status);
            $this->assertSame($user->id, $model->updated_by);
            $this->assertDatabaseHas('log_activities', [
                'user_id' => $user->id,
                'model' => $model::class,
            ]);
        }

        $this->assertSame(7, LogActivity::count());
    }

    public function test_public_statistics_only_return_active_frontend_fields(): void
    {
        $active = Statistic::create([
            'label' => 'Projets réalisés',
            'value' => 25,
            'unit' => '+',
        ]);
        Statistic::create([
            'label' => 'Statistique masquée',
            'value' => 10,
            'status' => false,
        ]);

        $this->getJson('/api/v1/statistics')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $active->id)
            ->assertJsonPath('data.0.value', '25.00')
            ->assertJsonMissingPath('data.0.status')
            ->assertJsonMissingPath('data.0.created_by')
            ->assertJsonMissingPath('data.0.updated_by')
            ->assertJsonMissingPath('data.0.created_at')
            ->assertJsonMissingPath('data.0.updated_at');
    }

    private function createUser(): User
    {
        return User::create([
            'name' => 'Administrateur',
            'telephone' => '620000005',
            'email' => 'status-admin@example.com',
            'password' => 'password',
        ]);
    }

    /**
     * @return array<string, Model>
     */
    private function createWebsiteResources(User $user): array
    {
        $category = Category::create([
            'libelle' => 'Applications web',
            'created_by' => $user->id,
        ]);
        $service = Service::create([
            'title' => 'Développement web',
            'short_description' => 'Applications web sur mesure',
            'description' => 'Conception et développement.',
            'created_by' => $user->id,
        ]);
        $client = Client::create([
            'first_name' => 'Mamadou',
            'last_name' => 'Diallo',
            'created_by' => $user->id,
        ]);
        $project = Project::create([
            'category_id' => $category->id,
            'service_id' => $service->id,
            'title' => 'Portail institutionnel',
            'short_description' => 'Présentation des activités.',
            'description' => 'Présentation détaillée des activités.',
            'created_by' => $user->id,
        ]);

        return [
            'clients' => $client,
            'partners' => Partner::create([
                'name' => 'Partenaire',
                'created_by' => $user->id,
            ]),
            'projects' => $project,
            'services' => $service,
            'statistics' => Statistic::create([
                'label' => 'Projets réalisés',
                'value' => 25,
                'created_by' => $user->id,
            ]),
            'testimonials' => Testimonial::create([
                'project_id' => $project->id,
                'client_id' => $client->id,
                'content' => 'Une excellente collaboration.',
                'created_by' => $user->id,
            ]),
            'visions' => Vision::create([
                'title' => 'Notre vision',
                'description' => 'Construire des solutions durables.',
                'created_by' => $user->id,
            ]),
        ];
    }
}
