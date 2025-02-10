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
        $apiKey = Setting::where('key', 'twocaptcha_api_key')->value('value');
        $cloudRunUrl = Setting::where('key', 'google_cloud_run_url')->value('value');

        if (!$cloudRunUrl) {
            $this->error("Cloud Run URL not found in settings.");
            Log::error("Cloud Run URL is missing from settings.");
            return Command::FAILURE;
        }

        foreach ($links as $link) {
            try {
                $result = $this->fetchLogValue($cloudRunUrl, $link, $apiKey);

                if ($result && isset($result['logsCount'])) {
                    // Save the logs in the LinkStat table
                    LinkStat::create([
                        'link_id' => $link->id,
                        'log' => $result['logsCount'],
                    ]);

                    $this->info("Updated stats for link ID: {$link->id} - Logs: {$result['logsCount']}");
                }
            } catch (\Exception $e) {
                $this->error("Error updating link ID: {$link->id} - " . $e->getMessage());
                Log::error("Error updating link stats", [
                    'link_id' => $link->id, 
                    'error' => $e->getMessage()
                ]);
            }
        }

        return Command::SUCCESS;
    }

    /**
     * Fetch log value using the Node.js script
     */
    private function fetchLogValue(string $cloudRunUrl, Link $link, string $apiKey): ?array
    {
        // Attempt the original request directly to the link
        try {
            $originalResponse = Http::timeout(180)->get($link->url);
            if ($originalResponse->successful()) {
                $content = $originalResponse->body();

                // Use DomCrawler to parse the HTML content
                $crawler = new \Symfony\Component\DomCrawler\Crawler($content);
                $divNodes = $crawler->filter('div.font-weight-medium');

                $logsCount = null;
                // Iterate through each matching node to find one containing "логов"
                foreach ($divNodes as $node) {
                    $text = $node->textContent;
                    if (strpos($text, 'логов') !== false) {
                        // Extract the number preceding "логов"
                        if (preg_match('/(\d+)\s*логов/u', $text, $matches)) {
                            $logsCount = intval($matches[1]);
                            break; // Once found, break out of the loop
                        }
                    }
                }

                if ($logsCount !== null) {
                    Log::info("Stats fetched on original request");

                    return ['logsCount' => $logsCount];
                } else {
                    Log::warning("No logs count found using 'логов' reference.", ['link_id' => $link->id]);
                }
            } else {
                Log::warning("Original request to {$link->url} was not successful.", [
                    'link_id' => $link->id,
                    'status' => $originalResponse->status()
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Exception during original request to {$link->url}.", [
                'link_id' => $link->id,
                'error' => $e->getMessage()
            ]);
        }

        // Fallback: call the Cloud Run API if the original request failed or did not yield a logs count
        try {
            $response = Http::timeout(180)->post($cloudRunUrl, [
                'url' => $link->url,
                'api_key' => $apiKey,
            ]);

            $result = $response->json();

            if ($response->successful()) {
                return $result;
            }

            Log::error('Cloud Run API request failed', [
                'link_id' => $link->id,
                'status' => $response->status(),
                'response' => $result
            ]);
        } catch (\Exception $e) {
            Log::error('Error calling Cloud Run API', [
                'link_id' => $link->id,
                'error' => $e->getMessage()
            ]);
        }

        return null;
    }
}
