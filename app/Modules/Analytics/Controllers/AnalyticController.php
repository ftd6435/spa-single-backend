<?php

namespace App\Modules\Analytics\Controllers;

use App\Events\AnalyticEvent;
use App\Http\Controllers\Controller;
use App\Modules\Analytics\Models\Analytic;
use App\Traits\ApiResponses;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AnalyticController extends Controller
{
    use ApiResponses;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->filled('date_debut') && $request->filled('date_fin'))
            $analytics = Analytic::whereBetween('created_at', [$request->input('date_debut'), $request->input('date_fin')])->orderBy('created_at', 'desc')->get();
        elseif ($request->filled('date_debut'))
            $analytics = Analytic::where('created_at', '>=', $request->input('date_debut'))->orderBy('created_at', 'desc')->get();
        elseif ($request->filled('date_fin'))
            $analytics = Analytic::where('created_at', '<=', $request->input('date_fin'))->orderBy('created_at', 'desc')->get();
        else
            $analytics = Analytic::where('created_at', Carbon::today())->orderBy('created_at', 'desc')->get();

        return $this->successResponse($analytics, "Analytics du site web chargé avec succès.");
    }

    public function track(Request $request)
    {
        AnalyticEvent::dispatch(
            $request->input('visitor_id'),
            $request->input('path'),
            $request->input('referrer'),
            $request->ip(),
            $request->userAgent(),
        );

        return $this->noContentSuccessResponse();
    }
}
