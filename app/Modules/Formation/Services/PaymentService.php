<?php

namespace App\Modules\Formation\Services;

use App\Modules\Formation\Enums\ParticipationStatus;
use App\Modules\Formation\Models\Participation;
use App\Modules\Formation\Models\Payment;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function create(Participation $participation, array $data, ?int $userId): Payment
    {
        return DB::transaction(function () use ($participation, $data, $userId) {
            $lockedParticipation = Participation::query()
                ->whereKey($participation->id)
                ->lockForUpdate()
                ->firstOrFail();

            $payment = $lockedParticipation->payments()->create($data + [
                'created_by' => $userId,
            ]);

            $this->recalculate($lockedParticipation);

            return $payment->fresh(['createdBy', 'updatedBy']);
        });
    }

    public function update(Payment $payment, array $data, ?int $userId): Payment
    {
        return DB::transaction(function () use ($payment, $data, $userId) {
            $lockedPayment = Payment::query()
                ->whereKey($payment->id)
                ->lockForUpdate()
                ->firstOrFail();

            $participation = Participation::query()
                ->whereKey($lockedPayment->participation_id)
                ->lockForUpdate()
                ->firstOrFail();

            $lockedPayment->update($data + ['updated_by' => $userId]);
            $this->recalculate($participation);

            return $lockedPayment->fresh(['createdBy', 'updatedBy']);
        });
    }

    public function delete(Payment $payment): void
    {
        DB::transaction(function () use ($payment) {
            $lockedPayment = Payment::query()
                ->whereKey($payment->id)
                ->lockForUpdate()
                ->firstOrFail();

            $participation = Participation::query()
                ->whereKey($lockedPayment->participation_id)
                ->lockForUpdate()
                ->firstOrFail();

            $lockedPayment->delete();
            $this->recalculate($participation);
        });
    }

    private function recalculate(Participation $participation): void
    {
        $total = $participation->payments()->sum('montant');
        $updates = ['frais_inscription_paye' => $total];

        if (
            $participation->status === ParticipationStatus::EnAttente
            && $this->toMinorUnits($total) >= $this->toMinorUnits($participation->frais_inscription_requis)
        ) {
            $updates['status'] = ParticipationStatus::Validee;
        }

        // Une participation déjà validée n'est jamais rétrogradée automatiquement.
        $participation->update($updates);
    }

    private function toMinorUnits(string|int|float $amount): int
    {
        return (int) round((float) $amount * 100);
    }
}
