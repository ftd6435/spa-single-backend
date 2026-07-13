<?php

namespace Tests\Feature\Formation;

use App\Modules\Formation\Enums\FormationStatus;
use App\Modules\Formation\Enums\ParticipationStatus;
use App\Modules\Formation\Models\Participant;
use App\Modules\Formation\Models\Participation;
use App\Modules\Formation\Models\Payment;

class ParticipationFeatureTest extends FormationTestCase
{
    public function test_public_registration_accepts_past_dates_and_snapshots_required_fee(): void
    {
        $formation = $this->createFormation([
            'status' => FormationStatus::EnCours,
            'date_debut' => now()->subWeek()->toDateString(),
            'date_fin' => now()->addWeek()->toDateString(),
            'date_fin_inscription' => now()->subMonth()->toDateString(),
            'frais_inscription' => 125000,
        ]);

        $this->postJson("/api/v1/formations/{$formation->id}/participations", $this->participantPayload())
            ->assertOk()
            ->assertJsonPath('data.status', 'en_attente');

        $this->assertDatabaseHas('participants', ['telephone' => '+224620000001']);
        $this->assertDatabaseHas('participations', [
            'formation_id' => $formation->id,
            'frais_inscription_requis' => 125000,
            'frais_inscription_paye' => 0,
        ]);
    }

    public function test_existing_participant_is_reused_without_overwriting_public_information(): void
    {
        $participant = Participant::create([
            'nom' => 'Diallo',
            'prenom' => 'Aminata',
            'telephone' => '+224 620-000-002',
            'adresse' => 'Ancienne adresse',
        ]);
        $firstFormation = $this->createFormation();
        $secondFormation = $this->createFormation(['libelle' => 'Deuxième formation']);

        $this->postJson("/api/v1/formations/{$firstFormation->id}/participations", [
            'nom' => 'Nom remplacé',
            'prenom' => 'Prénom remplacé',
            'telephone' => '+224 (620) 000-002',
            'adresse' => 'Nouvelle adresse',
        ])->assertOk();

        $this->postJson("/api/v1/formations/{$secondFormation->id}/participations", [
            'nom' => 'Autre',
            'prenom' => 'Autre',
            'telephone' => '+224620000002',
        ])->assertOk();

        $this->assertDatabaseCount('participants', 1);
        $this->assertDatabaseCount('participations', 2);
        $this->assertSame('Diallo', $participant->fresh()->nom);
        $this->assertSame('Ancienne adresse', $participant->fresh()->adresse);
    }

    public function test_duplicate_active_participation_is_rejected(): void
    {
        $formation = $this->createFormation();
        $payload = $this->participantPayload(['telephone' => '620000003']);

        $this->postJson("/api/v1/formations/{$formation->id}/participations", $payload)->assertOk();
        $this->postJson("/api/v1/formations/{$formation->id}/participations", $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('telephone');

        $this->assertDatabaseCount('participations', 1);
    }

    public function test_soft_deleted_participant_and_participation_are_restored_with_history(): void
    {
        $formation = $this->createFormation();
        $participant = Participant::create([
            'nom' => 'Camara',
            'prenom' => 'Moussa',
            'telephone' => '620000004',
        ]);
        $participation = Participation::create([
            'formation_id' => $formation->id,
            'participant_id' => $participant->id,
            'frais_inscription_requis' => 100000,
            'frais_inscription_paye' => 30000,
            'status' => ParticipationStatus::Abandonnee,
        ]);
        Payment::create([
            'participation_id' => $participation->id,
            'montant' => 30000,
            'methode_paiement' => 'Espèces',
            'date_paiement' => now()->toDateString(),
        ]);
        $participation->delete();
        $participant->delete();

        $this->postJson("/api/v1/formations/{$formation->id}/participations", [
            'nom' => 'Nouveau nom ignoré',
            'prenom' => 'Nouveau prénom ignoré',
            'telephone' => '620-000-004',
        ])->assertOk()
            ->assertJsonPath('data.id', $participation->id)
            ->assertJsonPath('data.status', 'abandonnee');

        $this->assertFalse($participant->fresh()->trashed());
        $this->assertFalse($participation->fresh()->trashed());
        $this->assertSame('30000.00', $participation->fresh()->frais_inscription_paye);
        $this->assertDatabaseCount('participants', 1);
        $this->assertDatabaseCount('participations', 1);
        $this->assertDatabaseCount('payments', 1);
    }

    public function test_inactive_cancelled_finished_and_full_formations_reject_registration(): void
    {
        $inactive = $this->createFormation(['is_active' => false]);
        $cancelled = $this->createFormation(['libelle' => 'Annulée', 'status' => FormationStatus::Annulee]);
        $finished = $this->createFormation(['libelle' => 'Terminée', 'status' => FormationStatus::Terminee]);
        $full = $this->createFormation(['libelle' => 'Complète', 'nombre_places' => 1]);
        $existing = Participant::create([
            'nom' => 'Déjà',
            'prenom' => 'Inscrit',
            'telephone' => '620000099',
        ]);
        Participation::create([
            'formation_id' => $full->id,
            'participant_id' => $existing->id,
            'frais_inscription_requis' => $full->frais_inscription,
        ]);

        foreach ([$inactive, $cancelled, $finished, $full] as $index => $formation) {
            $this->postJson(
                "/api/v1/formations/{$formation->id}/participations",
                $this->participantPayload(['telephone' => '62000001'.$index])
            )->assertUnprocessable()->assertJsonValidationErrors('formation');
        }
    }

    public function test_public_registration_response_never_exposes_personal_or_payment_data(): void
    {
        $formation = $this->createFormation();
        $participant = Participant::create([
            'nom' => 'Personne privée',
            'prenom' => 'Prénom privé',
            'telephone' => '620000050',
            'adresse' => 'Adresse confidentielle',
            'avatar_path' => 'avatar-prive.jpg',
        ]);
        $participation = Participation::create([
            'formation_id' => $formation->id,
            'participant_id' => $participant->id,
            'frais_inscription_requis' => 100000,
            'frais_inscription_paye' => 25000,
        ]);
        Payment::create([
            'participation_id' => $participation->id,
            'montant' => 25000,
            'methode_paiement' => 'Méthode confidentielle',
            'date_paiement' => now()->toDateString(),
            'commentaire' => 'Commentaire confidentiel',
        ]);
        $participation->delete();

        $response = $this->postJson("/api/v1/formations/{$formation->id}/participations", [
            'nom' => 'Nom envoyé mais ignoré',
            'prenom' => 'Prénom envoyé mais ignoré',
            'telephone' => '620-000-050',
            'adresse' => 'Nouvelle adresse ignorée',
        ])->assertOk();

        $this->assertSame(
            ['id', 'formation_id', 'status', 'created_at'],
            array_keys($response->json('data'))
        );
        $response
            ->assertJsonMissingPath('data.participant')
            ->assertJsonMissingPath('data.participant_id')
            ->assertJsonMissingPath('data.payments')
            ->assertJsonMissingPath('data.telephone')
            ->assertJsonMissingPath('data.adresse')
            ->assertJsonMissingPath('data.avatar_url')
            ->assertJsonMissingPath('data.frais_inscription_paye');
    }

    public function test_inactive_formation_category_rejects_registration(): void
    {
        $category = $this->createCategory(['is_active' => false]);
        $formation = $this->createFormation(['formation_category_id' => $category->id]);

        $this->postJson(
            "/api/v1/formations/{$formation->id}/participations",
            $this->participantPayload(['telephone' => '620000060'])
        )->assertUnprocessable()->assertJsonValidationErrors('formation');
    }

    public function test_soft_deleted_formation_category_rejects_registration(): void
    {
        $category = $this->createCategory();
        $formation = $this->createFormation(['formation_category_id' => $category->id]);
        $category->delete();

        $this->postJson(
            "/api/v1/formations/{$formation->id}/participations",
            $this->participantPayload(['telephone' => '620000061'])
        )->assertUnprocessable()->assertJsonValidationErrors('formation');
    }

    private function participantPayload(array $overrides = []): array
    {
        return array_merge([
            'nom' => 'Bah',
            'prenom' => 'Fatoumata',
            'telephone' => '+224 (620) 000-001',
            'adresse' => 'Conakry',
        ], $overrides);
    }
}
