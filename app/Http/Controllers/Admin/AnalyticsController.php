<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoginLog;
use App\Models\Post;
use App\Models\SiteVisit;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class AnalyticsController extends Controller
{
    public function index(Request $request): Response
    {
        $days = (int) $request->input('days', 30);
        $days = in_array($days, [7, 30, 90], true) ? $days : 30;

        $start = now()->subDays($days - 1)->startOfDay();
        $end = now()->endOfDay();

        return Inertia::render('Admin/Analytics/Index', [
            'days' => $days,
            'series' => [
                'visitors' => $this->fillDateRange(
                    SiteVisit::query()
                        ->whereBetween('visited_date', [$start->toDateString(), $end->toDateString()])
                        ->selectRaw('visited_date as date, COUNT(*) as count')
                        ->groupBy('visited_date')
                        ->pluck('count', 'date'),
                    $start,
                    $end
                ),
                'recipes' => $this->fillDateRange(
                    Post::query()
                        ->whereBetween('created_at', [$start, $end])
                        ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                        ->groupBy('date')
                        ->pluck('count', 'date'),
                    $start,
                    $end
                ),
                'logins' => $this->fillDateRange(
                    LoginLog::query()
                        ->whereBetween('created_at', [$start, $end])
                        ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                        ->groupBy('date')
                        ->pluck('count', 'date'),
                    $start,
                    $end
                ),
            ],
            'totals' => [
                'visitors' => SiteVisit::whereBetween('visited_date', [$start->toDateString(), $end->toDateString()])->count(),
                'recipes' => Post::whereBetween('created_at', [$start, $end])->count(),
                'logins' => LoginLog::whereBetween('created_at', [$start, $end])->count(),
            ],
        ]);
    }

    /**
     * Détail d'une journée précise pour un graphique donné — appelé au clic sur
     * un point. Les visiteurs restent anonymes (tracking par session, sans lien
     * vers un compte) : seul le total leur est renvoyé, jamais une liste.
     */
    public function details(Request $request)
    {
        $data = $request->validate([
            'metric' => ['required', 'in:visitors,recipes,logins'],
            'date' => ['required', 'date_format:Y-m-d'],
        ]);

        $date = $data['date'];

        return match ($data['metric']) {
            'visitors' => response()->json([
                'metric' => 'visitors',
                'count' => SiteVisit::whereDate('visited_date', $date)->count(),
                'items' => null, // anonyme — pas de détail individuel disponible
            ]),
            'recipes' => response()->json([
                'metric' => 'recipes',
                'items' => Post::query()
                    ->whereDate('created_at', $date)
                    ->with('user:id,name')
                    ->orderBy('created_at')
                    ->get(['id', 'title', 'user_id', 'created_at', 'status'])
                    ->map(fn ($post) => [
                        'id' => $post->id,
                        'title' => $post->title,
                        'user_name' => $post->user?->name,
                        'status' => $post->status,
                        'time' => $post->created_at->format('H:i'),
                    ]),
            ]),
            'logins' => response()->json([
                'metric' => 'logins',
                'items' => LoginLog::query()
                    ->whereDate('created_at', $date)
                    ->with('user:id,name')
                    ->orderBy('created_at')
                    ->get()
                    ->map(fn ($log) => [
                        'user_name' => $log->user?->name ?? 'Compte supprimé',
                        'time' => $log->created_at->format('H:i'),
                    ]),
            ]),
        };
    }

    /**
     * Transforme un pluck('count', 'date') potentiellement troué (aucune ligne
     * certains jours) en série continue jour par jour avec des zéros explicites —
     * indispensable pour qu'un graphique en ligne ne saute pas de dates.
     *
     * @param  \Illuminate\Support\Collection<string, int>  $counts
     * @return array<int, array{date: string, count: int}>
     */
    private function fillDateRange($counts, Carbon $start, Carbon $end): array
    {
        $result = [];
        $cursor = $start->copy();

        while ($cursor->lte($end)) {
            $key = $cursor->toDateString();
            $result[] = ['date' => $key, 'count' => (int) ($counts[$key] ?? 0)];
            $cursor->addDay();
        }

        return $result;
    }
}
