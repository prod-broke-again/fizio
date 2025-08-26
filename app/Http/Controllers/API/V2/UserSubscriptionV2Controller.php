<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\V2;

use App\Http\Controllers\Controller;
use App\Http\Requests\V2\StoreUserSubscriptionV2Request;
use App\Http\Resources\V2\UserSubscriptionV2Resource;
use App\Models\UserSubscriptionV2;
use App\Services\V2\SubscriptionServiceV2;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class UserSubscriptionV2Controller extends Controller
{
    public function __construct(
        private readonly SubscriptionServiceV2 $subscriptionService
    ) {}

    /**
     * Получить текущую подписку пользователя
     */
    public function show(): JsonResponse
    {
        $subscription = $this->subscriptionService->getActiveSubscription(Auth::id());
        
        if (!$subscription) {
            return response()->json([
                'success' => true,
                'data' => null,
                'message' => 'У пользователя нет активной подписки'
            ]);
        }
        
        return response()->json([
            'success' => true,
            'data' => new UserSubscriptionV2Resource($subscription)
        ]);
    }
    
    /**
     * Создать новую подписку
     */
    public function store(StoreUserSubscriptionV2Request $request): JsonResponse
    {
        $subscription = $this->subscriptionService->createSubscription(
            Auth::id(),
            $request->validated()
        );
        
        return response()->json([
            'success' => true,
            'message' => 'Подписка успешно создана',
            'data' => new UserSubscriptionV2Resource($subscription)
        ], 201);
    }
    
    /**
     * Отменить подписку
     */
    public function cancel(): JsonResponse
    {
        $cancelled = $this->subscriptionService->cancelSubscription(Auth::id());
        
        if (!$cancelled) {
            return response()->json([
                'success' => false,
                'message' => 'Не удалось отменить подписку'
            ], 400);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Подписка успешно отменена'
        ]);
    }
    
    /**
     * Проверить статус подписки
     */
    public function status(): JsonResponse
    {
        $status = $this->subscriptionService->getSubscriptionStatus(Auth::id());
        
        return response()->json([
            'success' => true,
            'data' => $status
        ]);
    }
}
