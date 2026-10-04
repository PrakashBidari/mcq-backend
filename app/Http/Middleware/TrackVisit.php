<?php

namespace App\Http\Middleware;

use App\Models\DailyVisit;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

// Records who used the app today, for the dashboard's visitor / active-user charts:
// one daily_visits row per device per day, tagged with the user once that device makes
// a signed-in request. The cache guards keep this to a single write per device (and
// one per token) per day rather than one per API call.
class TrackVisit
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Analytics must never break or slow down a real API response
        try {
            $this->record($request);
        } catch (\Throwable $e) {
            report($e);
        }

        return $response;
    }

    private function record(Request $request): void
    {
        $today = now()->toDateString();
        $untilMidnight = now()->endOfDay();
        $visitorKey = sha1($request->ip() . '|' . $request->userAgent());

        if (Cache::add("visit:{$today}:{$visitorKey}", true, $untilMidnight)) {
            DailyVisit::insertOrIgnore([
                'visit_date'  => $today,
                'visitor_key' => $visitorKey,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }

        $token = $request->bearerToken();

        if ($token && Cache::add("visit-user:{$today}:" . sha1($token), true, $untilMidnight)) {
            $user = auth('sanctum')->user();

            if ($user) {
                DailyVisit::where('visit_date', $today)
                    ->where('visitor_key', $visitorKey)
                    ->whereNull('user_id')
                    ->update(['user_id' => $user->id, 'updated_at' => now()]);
            }
        }
    }
}
