<?php

namespace App\Listeners;

use App\Events\AnalyticEvent;
use App\Modules\Analytics\Models\Analytic;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Jenssegers\Agent\Agent;

class AnalyticListener implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(AnalyticEvent $event)
    {
        $agent = new Agent(); // via jenssegers/agent
        $agent->setUserAgent($event->userAgent);

        $location = geoip($event->ip); // via torann/geoip

        Analytic::create([
            'visitor_id' => $event->visitorId,
            'path'       => $event->path,
            'referrer'   => $event->referrer ?? null,
            'device'     => $agent->isMobile() ? 'mobile' : ($agent->isTablet() ? 'tablet' : 'desktop'),
            'browser'    => $agent->browser(),
            'os'         => $agent->platform(),
            'country'    => $location->iso_code ?? null,
            'ip_hash'    => hash('sha256', $event->ip . config('app.key')), // anonymisation, pas d'IP brute stockée
        ]);
    }
}
