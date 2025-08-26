<?php

declare(strict_types=1);

namespace App\Http\Resources\V2;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserSubscriptionV2Resource extends JsonResource
{
    /**
     * Преобразовать ресурс в массив
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'subscription_type' => $this->subscription_type,
            'status' => $this->status,
            'starts_at' => $this->starts_at?->toISOString(),
            'expires_at' => $this->expires_at?->toISOString(),
            'days_remaining' => $this->expires_at ? max(0, now()->diffInDays($this->expires_at, false)) : 0,
            'is_active' => $this->status === 'active' && $this->expires_at > now(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
