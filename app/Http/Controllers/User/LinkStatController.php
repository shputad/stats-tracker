<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Link;
use App\Models\LinkStat;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LinkStatController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Link $link, Request $request)
    {
        $statsPerPage = Setting::where('key', 'stats_per_page')->value('value');
        $statsUpdateInterval = Setting::where('key', 'link_stats_update_interval')->value('value');
        $perPage = $request->get('per_page', $statsPerPage ?: 25);

        $statsPaginator = $link->linkStats()
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        $statsWithDifferences = $statsPaginator->getCollection()->map(function ($stat, $index) use ($link, $statsPaginator) {
            $baseLogsType = $link->base_logs_type ?: 'log';

            // Get the log value from the oldest record available in the last 10 minutes
            $logsBefore10Minutes = $link->linkStats()
                ->whereBetween('created_at', [$stat->created_at->copy()->subMinutes(10)->startOfMinute(), $stat->created_at->copy()->startOfMinute()])
                ->orderBy('created_at', 'asc')
                ->value($baseLogsType) ?? 0;

            // Get the log value from the oldest record available in the last hour
            $logsBeforeHour = $link->linkStats()
                ->whereBetween('created_at', [$stat->created_at->copy()->subMinutes(60)->startOfMinute(), $stat->created_at->copy()->startOfMinute()])
                ->orderBy('created_at', 'asc')
                ->value($baseLogsType) ?? 0;

            // Check if the current stat is the first stat of the hour
            $isFirstStatOfHour = !$link->linkStats()
                ->where('created_at', '<', $stat->created_at)
                ->whereBetween('created_at', [
                    $stat->created_at->startOfHour(),
                    $stat->created_at->endOfHour(),
                ])
                ->exists();

            // Get the log value from the oldest record available today
            $logsBeforeToday = $link->linkStats()
                ->whereDate('created_at', $stat->created_at->toDateString())
                ->orderBy('created_at', 'asc')
                ->value($baseLogsType) ?? 0;

            // For the first record, ensure differences are zero
            if ($index === ($statsPaginator->getCollection()->count() - 1)) {
                $logsBefore10Minutes = $stat->log;
                $logsBeforeHour = $isFirstStatOfHour ? ($logsBeforeHour !== null ? $stat->log : null) : null;
            }

            return [
                'id' => $stat->id,
                'log' => $stat->$baseLogsType,
                'created_at' => $stat->created_at,
                'last_10_minutes_diff' => $stat->$baseLogsType - $logsBefore10Minutes,
                'last_hour_diff' => $isFirstStatOfHour ? ($logsBeforeHour !== null ? $stat->$baseLogsType - $logsBeforeHour : null) : null,
                'today_diff' => $stat->$baseLogsType - $logsBeforeToday,
            ];
        });

        $statsPaginator->setCollection($statsWithDifferences);

        return Inertia::render('User/Links/Stats/Index', [
            'link' => $link,
            'stats' => $statsPaginator,
            'settings' => ['link_stats_update_interval' => $statsUpdateInterval]
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }
}
