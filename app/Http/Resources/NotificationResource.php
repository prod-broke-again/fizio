<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class NotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'title' => $this->title,
            'message' => $this->message,
            'type' => $this->type,
            'read' => (bool) $this->read,
            'action' => $this->action,
            'action_url' => $this->action_url,
            'created_at' => Carbon::parse($this->created_at)->setTimezone('Europe/Moscow')->toDateTimeString(),
            'updated_at' => Carbon::parse($this->updated_at)->setTimezone('Europe/Moscow')->toDateTimeString(),
        ];
    }
}
