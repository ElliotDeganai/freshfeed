<?php

namespace App\Http\Middleware;

use App\Models\SiteVisit;
use Closure;
use Illuminate\Http\Request;

class TrackSiteVisit
{
    public function handle(Request $request, Closure $next)
    {
        // Ne compte que les vraies pages vues par un visiteur (GET, pas les appels
        // fetch() du scroll infini/API, pas les assets, pas la zone admin — sinon
        // le propriétaire du site fausserait ses propres statistiques).
        if (
            $request->isMethod('GET')
            && ! $request->wantsJson()
            && ! $request->is('admin*')
            && ! $request->is('storage*')
            && ! $request->is('build*')
        ) {
            $visitorKey = $request->session()->getId();

            // insertOrIgnore : silencieux si déjà présent aujourd'hui pour ce visiteur
            // (contrainte unique sur visitor_key + visited_date) — pas de requête de
            // vérification préalable, une seule requête légère à chaque passage.
            SiteVisit::query()->insertOrIgnore([
                'visitor_key' => $visitorKey,
                'visited_date' => now()->toDateString(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return $next($request);
    }
}
