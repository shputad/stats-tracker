<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Link;
use App\Models\LinkStat;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Carbon;

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
            $createdAt = Carbon::parse($stat->created_at);
            $baseLogsType = $link->base_logs_type ?: 'log';

            // Previous stat before this one
            $previous = $link->linkStats()
                ->where('created_at', '<', $createdAt)
                ->orderBy('created_at', 'desc')
                ->first();

            $diffAmount = null;
            $timeDiffTooltip = null;

            if ($previous) {
                $prevAt = Carbon::parse($previous->created_at);
                $diffMinutes = $prevAt->diffInMinutes($createdAt);
                $diffAmount = $stat->$baseLogsType - $previous->$baseLogsType;

                if ($diffMinutes >= 11) {
                    $timeDiffTooltip = 'Last stat was ' . $prevAt->diffForHumans($createdAt);
                }
            }

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
                'last_10_minutes_diff' => $diffAmount,
                'last_10_minutes_tooltip' => $timeDiffTooltip,
                'last_hour_diff' => $isFirstStatOfHour ? ($stat->$baseLogsType - $logsBeforeHour) : null,
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
