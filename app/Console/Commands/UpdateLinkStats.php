<?php

namespace App\Console\Commands;

use App\Models\Link;
use App\Models\LinkStat;
use App\Models\Setting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

class UpdateLinkStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stats:update-links';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch and update link stats periodically.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $links = Link::where('status', 'active')->get();

        foreach ($links as $link) {
            try {
                $result = $this->fetchLogValue($link);

                if ($result && isset($result['logs'])) {
                    // Save the logs in the LinkStat table
                    LinkStat::create([
                        'link_id' => $link->id,
                        'log' => $result['logs'],
                    ]);

                    $this->info("Updated stats for link ID: {$link->id} - Logs: {$result['logs']}");
                }
            } catch (\Exception $e) {
                $this->error("Error updating link ID: {$link->id} - " . $e->getMessage());
                Log::error("Error updating link stats", ['link_id' => $link->id, 'error' => $e->getMessage()]);
            }
        }

        return Command::SUCCESS;
    }

    /**
     * Fetch log value using the Node.js script
     */
    private function fetchLogValue(Link $link): ?array
    {
        $apiKey = Setting::where('key', 'twocaptcha_api_key')->value('value');
        $scriptPath = base_path('resources/js/captcha/fetch-logs.js');

        // Use node command to fetch logs
        $command = "node {$scriptPath} '{$link->url}' '{$apiKey}'";
        $output = shell_exec($command);

        // Log the output for debugging purposes
        Log::info('Node.js script output', ['output' => $output]);

        // Parse the JSON output from the script
        $result = json_decode($output, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            return $result;
        }

        Log::error('Failed to parse Node.js script output', ['output' => $output]);
        return null;
    }
}
