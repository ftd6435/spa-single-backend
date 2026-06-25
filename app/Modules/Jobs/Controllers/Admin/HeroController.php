<?php

namespace App\Modules\Jobs\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Jobs\Models\Hero;
use App\Modules\Jobs\Requests\StoreHeroRequest;
use App\Modules\Jobs\Requests\UpdateHeroRequest;
use App\Modules\Jobs\Resources\HeroResource;

class HeroController extends Controller
{
    public function index()
    {
        return HeroResource::collection(
            Hero::with('page')->latest()->paginate(10)
        );
    }

    public function store(StoreHeroRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('file')) {
            $data['file'] = $request->file('file')
                ->store('heroes/files', 'public');
        }

        $hero = Hero::create($data);

        return new HeroResource($hero);
    }

    public function show(Hero $hero)
    {
        return new HeroResource($hero->load('page'));
    }

    public function update(UpdateHeroRequest $request, Hero $hero)
    {
        $data = $request->validated();

        if ($request->hasFile('file')) {
            $data['file'] = $request->file('file')
                ->store('heroes/files', 'public');
        }

        $hero->update($data);

        return new HeroResource($hero);
    }

    public function destroy(Hero $hero)
    {
        $hero->delete();

        return response()->json([
            'message' => 'Hero deleted successfully.'
        ]);
    }
}