<?php

namespace App\Modules\Formation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Formation\Models\Participation;
use App\Modules\Formation\Models\Payment;
use App\Modules\Formation\Requests\StorePaymentRequest;
use App\Modules\Formation\Requests\UpdatePaymentRequest;
use App\Modules\Formation\Resources\PaymentResource;
use App\Modules\Formation\Services\PaymentService;
use App\Traits\ApiResponses;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    use ApiResponses;

    public function __construct(private readonly PaymentService $paymentService) {}

    public function index(string $participation)
    {
        $model = Participation::withTrashed()->find($participation);
        if (! $model) {
            return $this->errorResponse('Participation introuvable.');
        }

        $payments = $model->payments()
            ->withTrashed()
            ->with(['createdBy', 'updatedBy'])
            ->orderByDesc('date_paiement')
            ->get();

        return $this->successResponse(
            PaymentResource::collection($payments),
            'Liste des paiements chargée avec succès.'
        );
    }

    public function store(StorePaymentRequest $request, string $participation)
    {
        $model = Participation::find($participation);
        if (! $model) {
            return $this->errorResponse('Participation introuvable.');
        }

        $payment = $this->paymentService->create($model, $request->validated(), Auth::id());

        logActivity("Enregistrement d'un paiement", $request->validated(), $payment);

        return $this->successResponse(
            PaymentResource::make($payment),
            'Paiement enregistré avec succès.'
        );
    }

    public function show(string $payment)
    {
        $model = Payment::withTrashed()
            ->with(['createdBy', 'updatedBy'])
            ->find($payment);

        if (! $model) {
            return $this->errorResponse('Paiement introuvable.');
        }

        return $this->successResponse(
            PaymentResource::make($model),
            'Paiement chargé avec succès.'
        );
    }

    public function update(UpdatePaymentRequest $request, string $payment)
    {
        $model = Payment::find($payment);
        if (! $model) {
            return $this->errorResponse('Paiement introuvable.');
        }

        $oldValues = $model->toArray();
        $updatedPayment = $this->paymentService->update($model, $request->validated(), Auth::id());

        logActivity("Modification d'un paiement", [
            'old_value' => $oldValues,
            'new_value' => $request->validated(),
        ], $updatedPayment);

        return $this->successResponse(
            PaymentResource::make($updatedPayment),
            'Paiement modifié avec succès.'
        );
    }

    public function destroy(string $payment)
    {
        $model = Payment::find($payment);
        if (! $model) {
            return $this->errorResponse('Paiement introuvable.');
        }

        $oldValues = $model->toArray();
        $this->paymentService->delete($model);

        logActivity("Suppression d'un paiement", $oldValues, $model);

        return $this->noContentSuccessResponse('Paiement supprimé avec succès.');
    }
}
