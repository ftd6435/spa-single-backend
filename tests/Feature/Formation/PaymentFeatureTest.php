<?php

namespace Tests\Feature\Formation;

use App\Modules\Formation\Enums\ParticipationStatus;
use App\Modules\Formation\Models\Participant;
use App\Modules\Formation\Models\Participation;
use App\Modules\Formation\Models\Payment;

class PaymentFeatureTest extends FormationTestCase
{
    public function test_multiple_payments_are_cumulative_and_validate_waiting_participation(): void
    {
        $this->authenticate();
        $participation = $this->createParticipation(100000);

        $this->postJson("/api/v1/admin/participations/{$participation->id}/payments", $this->paymentPayload(30000))
            ->assertOk();
        $this->assertSame('30000.00', $participation->fresh()->frais_inscription_paye);
        $this->assertSame(ParticipationStatus::EnAttente, $participation->fresh()->status);

        $this->postJson("/api/v1/admin/participations/{$participation->id}/payments", $this->paymentPayload(70000))
            ->assertOk();

        $participation->refresh();
        $this->assertSame('100000.00', $participation->frais_inscription_paye);
        $this->assertSame(ParticipationStatus::Validee, $participation->status);
        $this->assertDatabaseCount('payments', 2);
    }

    public function test_historical_required_fee_is_unchanged_when_formation_price_changes(): void
    {
        $this->authenticate();
        $participation = $this->createParticipation(100000);
        $participation->formation->update(['frais_inscription' => 20000]);

        $this->postJson("/api/v1/admin/participations/{$participation->id}/payments", $this->paymentPayload(20000))
            ->assertOk();

        $participation->refresh();
        $this->assertSame('100000.00', $participation->frais_inscription_requis);
        $this->assertSame('20000.00', $participation->frais_inscription_paye);
        $this->assertSame(ParticipationStatus::EnAttente, $participation->status);

        $this->postJson("/api/v1/admin/participations/{$participation->id}/payments", $this->paymentPayload(80000))
            ->assertOk();
        $this->assertSame(ParticipationStatus::Validee, $participation->fresh()->status);
    }

    public function test_abandoned_and_finished_participations_are_never_auto_validated(): void
    {
        $this->authenticate();

        foreach ([ParticipationStatus::Abandonnee, ParticipationStatus::Terminee] as $index => $status) {
            $participation = $this->createParticipation(50000, $status, '62000020'.$index);
            $this->postJson(
                "/api/v1/admin/participations/{$participation->id}/payments",
                $this->paymentPayload(60000)
            )->assertOk();

            $this->assertSame($status, $participation->fresh()->status);
        }
    }

    public function test_payment_update_and_soft_delete_recalculate_without_downgrading_validated_status(): void
    {
        $this->authenticate();
        $participation = $this->createParticipation(100000);

        $response = $this->postJson(
            "/api/v1/admin/participations/{$participation->id}/payments",
            $this->paymentPayload(100000)
        )->assertOk();
        $payment = Payment::findOrFail($response->json('data.id'));
        $this->assertSame(ParticipationStatus::Validee, $participation->fresh()->status);

        $this->patchJson("/api/v1/admin/payments/{$payment->id}", [
            'montant' => 25000,
        ])->assertOk();

        $participation->refresh();
        $this->assertSame('25000.00', $participation->frais_inscription_paye);
        $this->assertSame(ParticipationStatus::Validee, $participation->status);

        $this->deleteJson("/api/v1/admin/payments/{$payment->id}")->assertOk();

        $participation->refresh();
        $this->assertSame('0.00', $participation->frais_inscription_paye);
        $this->assertSame(ParticipationStatus::Validee, $participation->status);
        $this->assertSoftDeleted('payments', ['id' => $payment->id]);
    }

    public function test_payment_validation_and_authentication_are_enforced(): void
    {
        $participation = $this->createParticipation(100000);

        $this->postJson("/api/v1/admin/participations/{$participation->id}/payments", $this->paymentPayload(1000))
            ->assertUnauthorized();

        $this->authenticate();
        $this->postJson("/api/v1/admin/participations/{$participation->id}/payments", [
            'montant' => 0,
            'methode_paiement' => '',
            'date_paiement' => 'invalid',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['montant', 'methode_paiement', 'date_paiement']);
    }

    public function test_payment_amounts_respect_decimal_precision_and_column_capacity(): void
    {
        $this->authenticate();

        foreach (['100', '100.5', '100.50'] as $index => $amount) {
            $participation = $this->createParticipation(100000, telephone: '62000030'.$index);

            $this->postJson(
                "/api/v1/admin/participations/{$participation->id}/payments",
                $this->paymentPayload($amount)
            )->assertOk();
        }

        $participation = $this->createParticipation(100000, telephone: '620000310');

        foreach (['100.999', '0.001', '10000000000000.00'] as $amount) {
            $this->postJson(
                "/api/v1/admin/participations/{$participation->id}/payments",
                $this->paymentPayload($amount)
            )->assertUnprocessable()->assertJsonValidationErrors('montant');
        }

        $payment = Payment::create([
            'participation_id' => $participation->id,
            'montant' => 50,
            'methode_paiement' => 'Espèces',
            'date_paiement' => now()->toDateString(),
        ]);

        $this->patchJson("/api/v1/admin/payments/{$payment->id}", [
            'montant' => '100.50',
        ])->assertOk();

        foreach (['100.999', '0.001', '10000000000000.00'] as $amount) {
            $this->patchJson("/api/v1/admin/payments/{$payment->id}", [
                'montant' => $amount,
            ])->assertUnprocessable()->assertJsonValidationErrors('montant');
        }
    }

    private function createParticipation(
        int $required,
        ParticipationStatus $status = ParticipationStatus::EnAttente,
        string $telephone = '620000200'
    ): Participation {
        $formation = $this->createFormation(['frais_inscription' => $required]);
        $participant = Participant::create([
            'nom' => 'Condé',
            'prenom' => 'Ibrahima',
            'telephone' => $telephone,
        ]);

        return Participation::create([
            'formation_id' => $formation->id,
            'participant_id' => $participant->id,
            'frais_inscription_requis' => $required,
            'frais_inscription_paye' => 0,
            'status' => $status,
        ]);
    }

    private function paymentPayload(string|int|float $amount): array
    {
        return [
            'montant' => $amount,
            'methode_paiement' => 'Espèces',
            'date_paiement' => now()->toDateString(),
            'commentaire' => 'Frais d’inscription uniquement',
        ];
    }
}
