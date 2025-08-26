<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\V2\SubscriptionServiceV2;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class CheckSubscriptionV2
{
    public function __construct(
        private readonly SubscriptionServiceV2 $subscriptionService
    ) {}

    /**
     * Обработать входящий запрос
     */
    public function handle(Request $request, Closure $next): Response|SymfonyResponse
    {
        // Проверяем, авторизован ли пользователь
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Требуется авторизация'
            ], 401);
        }

        $user = Auth::user();
        
        // Проверяем активную подписку
        if ($this->subscriptionService->isSubscriptionExpired($user->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Требуется активная подписка для доступа к этому контенту',
                'subscription_required' => true
            ], 403);
        }

        return $next($request);
    }
}
