<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AppleWatchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppleWatchController extends Controller
{
    protected $appleWatchService;

    public function __construct(AppleWatchService $appleWatchService)
    {
        $this->appleWatchService = $appleWatchService;
    }

    public function connect(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'device_token' => 'required|string',
        ]);

        $success = $this->appleWatchService->connect(
            auth()->user(),
            $validated['device_token']
        );

        return response()->json([
            'success' => $success,
            'message' => $success
                ? 'Apple Watch успешно подключен'
                : 'Не удалось подключить Apple Watch',
        ]);
    }

    public function sync(): JsonResponse
    {
        $success = $this->appleWatchService->sync(auth()->user());

        return response()->json([
            'success' => $success,
            'message' => $success
                ? 'Данные успешно синхронизированы'
                : 'Не удалось синхронизировать данные',
        ]);
    }
} 