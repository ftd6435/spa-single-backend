<?php

namespace App\Modules\Website\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Website\Models\Statistic;
use App\Modules\Website\Requests\StatisticRequest;
use App\Modules\Website\Resources\StatisticResource;
use App\Traits\ApiResponses;
use Illuminate\Support\Facades\Auth;

class StatisticController extends Controller
{
    use ApiResponses;

    public function index()
    {
        $statistics = Statistic::with('createdBy', 'updatedBy')
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->successResponse(
            StatisticResource::collection($statistics),
            'Liste des statistiques chargée avec succès.'
        );
    }

    public function publicIndex()
    {
        $statistics = Statistic::query()
            ->select('id', 'label', 'value', 'unit')
            ->where('status', true)
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->successResponse(
            StatisticResource::collection($statistics),
            'Liste des statistiques chargée avec succès.'
        );
    }

    public function store(StatisticRequest $request)
    {
        $data = $request->validated();
        $data['created_by'] = Auth::id();

        $statistic = Statistic::create($data);

        logActivity("Création d'une statistique", $data, $statistic);

        return $this->successResponse(
            StatisticResource::make($statistic->load('createdBy', 'updatedBy')),
            'Statistique créée avec succès.'
        );
    }

    public function show(string $id)
    {
        $statistic = Statistic::with('createdBy', 'updatedBy')->find($id);

        if (! $statistic) {
            return $this->errorResponse('Statistique introuvable.');
        }

        return $this->successResponse(
            StatisticResource::make($statistic),
            'Statistique chargée avec succès.'
        );
    }

    public function switchStatus(string $id)
    {
        $statistic = Statistic::find($id);

        if (! $statistic) {
            return $this->errorResponse('Statistique introuvable.');
        }

        $oldStatus = $statistic->status;
        $statistic->status = ! $oldStatus;
        $statistic->updated_by = Auth::id();
        $statistic->save();

        logActivity("Changement du statut d'une statistique", [
            'old_value' => ['status' => $oldStatus],
            'new_value' => ['status' => $statistic->status],
        ], $statistic);

        return $this->noContentSuccessResponse('Statut de la statistique mis à jour avec succès.');
    }

    public function update(StatisticRequest $request, string $id)
    {
        $statistic = Statistic::find($id);

        if (! $statistic) {
            return $this->errorResponse('Statistique introuvable.');
        }

        $data = $request->validated();
        $data['updated_by'] = Auth::id();

        $logData = [
            'old_value' => $statistic->toArray(),
            'new_value' => $data,
        ];

        $statistic->update($data);

        logActivity("Modification d'une statistique", $logData, $statistic);

        return $this->successResponse(
            StatisticResource::make($statistic->fresh()->load('createdBy', 'updatedBy')),
            'Statistique modifiée avec succès.'
        );
    }

    public function destroy(string $id)
    {
        $statistic = Statistic::find($id);

        if (! $statistic) {
            return $this->errorResponse('Statistique introuvable.');
        }

        logActivity("Suppression d'une statistique", $statistic->toArray(), $statistic);

        $statistic->delete();

        return $this->noContentSuccessResponse('Statistique supprimée avec succès.');
    }
}
