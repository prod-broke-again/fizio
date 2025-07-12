<?php

namespace App\Services;

use App\Models\User;
use App\Models\Progress;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AppleWatchService
{
    protected $apiKey;
    protected $apiUrl;

    public function __construct()
    {
        $this->apiKey = config('services.apple_watch.api_key');
        $this->apiUrl = config('services.apple_watch.api_url');
    }

    public function connect(User $user, string $deviceToken): bool
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])->post($this->apiUrl . '/connect', [
                'user_id' => $user->id,
                'device_token' => $deviceToken,
            ]);

            if ($response->successful()) {
                $user->update([
                    'apple_watch_connected' => true,
                    'apple_watch_token' => $deviceToken,
                ]);
                return true;
            }

            Log::error('Apple Watch connection failed', [
                'user_id' => $user->id,
                'response' => $response->json(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('Apple Watch connection error', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function sync(User $user): bool
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])->post($this->apiUrl . '/sync', [
                'user_id' => $user->id,
                'device_token' => $user->apple_watch_token,
            ]);

            if ($response->successful()) {
                $data = $response->json();

                Progress::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'date' => now()->toDateString(),
                    ],
                    [
                        'calories' => $data['calories'] ?? 0,
                        'steps' => $data['steps'] ?? 0,
                        'workout_time' => $data['workout_time'] ?? 0,
                        'water_intake' => $data['water_intake'] ?? 0,
                    ]
                );

                return true;
            }

            Log::error('Apple Watch sync failed', [
                'user_id' => $user->id,
                'response' => $response->json(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('Apple Watch sync error', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
} 