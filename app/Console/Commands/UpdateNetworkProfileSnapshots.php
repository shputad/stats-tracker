<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\NetworkProfile;
use App\Models\NetworkProfileStat;
use App\Models\NetworkProfileSnapshot;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class UpdateNetworkProfileSnapshots extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stats:update-profile-snapshots';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Capture balance snapshots for network profiles with valid API support.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $takenAt = now();

        $profiles = NetworkProfile::with('networkChannel')
            ->whereHas('networkChannel', fn ($q) => $q->where('has_api', true))
            ->where(function ($query) {
                $query->whereNotNull('api_key')
                    ->orWhere(function ($q) {
                        $q->whereNotNull('api_username')->whereNotNull('api_password');
                    });
            })
            ->where('status', 'active')
            ->get();

        foreach ($profiles as $profile) {
            $balance = null;
            $channelName = strtolower($profile->networkChannel->name);

            switch ($channelName) {
                case 'bidvertiser':
                    $username = $profile->api_username;
                    $password = $profile->api_password;
                    $apiKey   = $profile->api_key;

                    if (!$username || !$password || !$apiKey) {
                        Log::warning("Missing Bidvertiser credentials for profile ID {$profile->id}");
                        continue 2;
                    }

                    $token = $this->getBidvertiserToken($username, $password, $profile->id);
                    if (!$token) continue 2;

                    usleep(800000);

                    $balance = $this->fetchBidvertiserBalance($token, $apiKey);
                    break;

                case 'galaksion':
                    $username = $profile->api_username;
                    $password = $profile->api_password;

                    if (!$username || !$password) {
                        Log::warning("Missing Galaksion credentials for profile ID {$profile->id}");
                        continue 2;
                    }

                    $token = $this->getGalaksionToken($username, $password, $profile->id);
                    if (!$token) continue 2;

                    $balance = $this->fetchGalaksionBalance($token);
                    break;

                case 'bidmag':
                    $apiKey = $profile->api_key;
                
                    if (!$apiKey) {
                        Log::warning("Missing BidMag API key for profile ID {$profile->id}");
                        continue 2;
                    }
                
                    $balance = $this->fetchBidmagBalance($apiKey);
                    break;

                case 'zeropark':
                    $apiKey = $profile->api_key;
                
                    if (!$apiKey) {
                        Log::warning("Missing Zeropark API token for profile ID {$profile->id}");
                        continue 2;
                    }
                
                    $balance = $this->fetchZeroparkBalance($apiKey);
                    break;

                default:
                    Log::info("No balance handler for channel '{$profile->networkChannel->name}' (Profile ID: {$profile->id})");
                    continue 2;
            }

            if (!is_null($balance)) {
                NetworkProfileSnapshot::create([
                    'profile_id' => $profile->id,
                    'balance' => $balance,
                    'taken_at' => $takenAt,
                ]);

                $today = Carbon::today();
                $stat = NetworkProfileStat::firstOrNew([
                    'profile_id' => $profile->id,
                    'date' => $today,
                ]);

                $isFirstStatToday = is_null($stat->opening_balance);

                if ($isFirstStatToday) {
                    $stat->opening_balance = $balance;
                }

                $stat->current_balance = $balance;
                $stat->closing_balance = $balance;
                $stat->save();

                // ✅ Only try syncing with last existing stat (not necessarily yesterday)
                if ($isFirstStatToday) {
                    $lastStat = NetworkProfileStat::where('profile_id', $profile->id)
                        ->where('date', '<', $today)
                        ->orderByDesc('date')
                        ->first();

                    if ($lastStat && $lastStat->closing_balance != $stat->opening_balance) {
                        $lastStat->closing_balance = $stat->opening_balance;
                        $lastStat->save();

                        Log::info("[{$profile->id}] Closing balance synced for {$lastStat->date} → {$stat->opening_balance}");
                    }
                }

                Log::info("[{$profile->id}] Balance recorded: {$balance}");
            } else {
                Log::warning("[{$profile->id}] Failed to retrieve balance.");
            }
        }

        return Command::SUCCESS;
    }

    public function getBidvertiserToken(string $username, string $password, int $profileId): ?string
    {
        $encoded = base64_encode("$username:$password");

        $response = Http::withHeaders([
            'Authorization' => "Basic {$encoded}",
            'Accept' => 'application/json'
        ])->post('https://my.bidvertiser.com/bdv/bidvertiser/api/adv/TOKEN/');

        if ($response->successful()) {
            $token = $response->json()['BDV_API']['AUTHORIZATION_TOKEN'] ?? null;

            if (!$token) {
                Log::warning("[{$profileId}] Token structure missing AUTHORIZATION_TOKEN", [
                    'response' => $response->json()
                ]);
            }

            return $token;
        }

        Log::error("[{$profileId}] Failed to fetch BidVertiser token", [
            'status' => $response->status(),
            'response' => $response->json()
        ]);

        return null;
    }

    public function fetchBidvertiserBalance(string $token, string $apiKey): ?float
    {
        $response = Http::withHeaders([
            'Authorization' => "Bearer {$token}",
            'api_key' => $apiKey,
            'Accept' => 'application/json'
        ])->get('https://my.bidvertiser.com/bdv/bidvertiser/api/adv/BALANCE/');
    
        if ($response->successful()) {
            $json = $response->json();
            $balance = $json['BDV_API']['RESULTS']['BALANCE']['AMOUNT'] ?? null;
    
            if (is_null($balance) && isset($json['BDV_API']['ERROR']['NOTE']) &&
                str_contains($json['BDV_API']['ERROR']['NOTE'], 'Multithreaded')) {

                Log::info('Retrying balance fetch after delay due to multithreading warning');
                sleep(1);

                return $this->fetchBidvertiserBalance($token, $apiKey); // One retry
            }
    
            return $balance;
        }
    
        Log::error("BidVertiser balance API call failed", [
            'status' => $response->status(),
            'response' => $response->json()
        ]);
    
        return null;
    }    

    private function getGalaksionToken(string $email, string $password, int $profileId): ?string
    {
        try {
            $response = Http::timeout(30)->post('https://ssp2-api.galaksion.com/api/v1/auth', [
                'email' => $email,
                'password' => $password,
            ]);

            if ($response->successful()) {
                return $response->json()['token'] ?? null;
            }

            Log::error("Galaksion auth failed", [
                'profile_id' => $profileId,
                'status' => $response->status(),
                'response' => $response->json(),
            ]);
        } catch (\Exception $e) {
            Log::error("Galaksion token fetch error", [
                'profile_id' => $profileId,
                'error' => $e->getMessage(),
            ]);
        }

        return null;
    }

    private function fetchGalaksionBalance(string $token): ?float
    {
        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'X-Auth-Token' => $token,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ])
                ->get('https://ssp2-api.galaksion.com/api/v1/advertiser/balance');

            if ($response->successful()) {
                return floatval($response->json()['balance'] ?? 0);
            }

            Log::error("Galaksion balance fetch failed", [
                'status' => $response->status(),
                'response' => $response->json(),
            ]);
        } catch (\Exception $e) {
            Log::error("Error fetching Galaksion balance", [
                'error' => $e->getMessage(),
            ]);
        }

        return null;
    }

    private function fetchBidmagBalance(string $apiKey): ?float
    {
        try {
            $response = Http::timeout(30)
                ->withHeaders(['Accept' => 'application/json'])
                ->get("https://bidmag.net/api/me/balance", [
                    'api-key' => $apiKey,
                ]);

            if ($response->successful()) {
                return floatval($response->json()['balance'] ?? 0);
            }

            Log::error("BidMag balance fetch failed", [
                'status' => $response->status(),
                'response' => $response->json(),
            ]);
        } catch (\Exception $e) {
            Log::error("Error fetching BidMag balance", [
                'error' => $e->getMessage(),
            ]);
        }

        return null;
    }

    private function fetchZeroparkBalance(string $apiKey): ?float
    {
        $apiKey = trim(str_replace(["\r", "\n"], '', $apiKey));

        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'api-token' => $apiKey,
                    'Accept' => '*/*',
                ])
                ->get('https://panel.zeropark.com/api/account/details');

            if ($response->successful()) {
                return floatval($response->json()['accountBalance'] ?? 0);
            }

            Log::error("Zeropark balance fetch failed", [
                'status' => $response->status(),
                'response' => $response->json(),
            ]);
        } catch (\Exception $e) {
            Log::error("Error fetching Zeropark balance", [
                'error' => $e->getMessage(),
            ]);
        }

        return null;
    }
}
