<?php

namespace App\Modules\Sondage\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Sondage\Models\Vote;
use App\Modules\Sondage\Requests\StoreVoteRequest;
use App\Modules\Sondage\Resources\VoteResource;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class VoteController extends Controller
{
    use ApiResponses;

    // Route admin — liste des votes, filtrable par sondage (?init_sondage_id=)
    public function index(Request $request)
    {
        $query = Vote::with('votant');

        if ($request->filled('init_sondage_id')) {
            $query->where('init_sondage_id', $request->input('init_sondage_id'));
        }

        return $this->successResponse(
            VoteResource::collection($query->orderBy('created_at', 'desc')->get()),
            "Liste des votes chargée avec succès."
        );
    }

    // Route publique — un votant soumet son pronostic pour un sondage
    public function store(StoreVoteRequest $request)
    {
        $data = $request->validated();
        $data['reference'] = strtoupper(Str::random(10));
        $data['is_winner'] = false;

        $vote = Vote::create($data);

        logActivity("Création d'un vote", $data, $vote);

        return $this->successResponse(new VoteResource($vote->load('votant')), "Vote enregistré avec succès.");
    }

    // Route admin — détail d'un vote
    public function show(string $id)
    {
        $vote = Vote::with('votant')->find($id);

        if (! $vote) {
            return $this->errorResponse("Vote introuvable.");
        }

        return $this->successResponse(new VoteResource($vote), "Vote chargé avec succès.");
    }
}
