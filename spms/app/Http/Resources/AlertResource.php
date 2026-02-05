<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AlertResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'severity' => $this->severity,
            'message' => $this->message,
            'entity_type' => $this->entity_type,
            'entity_id' => $this->entity_id,
            'resolved' => $this->resolved,
            'resolved_at' => optional($this->resolved_at)->toDateTimeString(),
            'timestamp' => optional($this->created_at)->toDateTimeString(),
        ];
    }
}
