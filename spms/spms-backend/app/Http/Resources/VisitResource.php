<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class VisitResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'visit_type' => $this->visit_type,
            'visitor' => new VisitorResource($this->whenLoaded('visitor')),
            'vehicle' => new VehicleResource($this->whenLoaded('vehicle')),
            'driver' => new DriverResource($this->whenLoaded('driver')),
            'purpose' => $this->purpose,
            'person_to_visit' => $this->person_to_visit,
            'department' => $this->department,
            'additional_notes' => $this->additional_notes,
            'assigned_bay' => $this->assigned_bay,
            'checked_in_at' => $this->checked_in_at,
            'plate_number' => $this->plate_number,
            'checked_out_at' => $this->checked_out_at,
            'status' => $this->status,
            'has_discrepancies' => $this->has_discrepancies,
            'goods_verified' => $this->goods_verified,
            'weight_checked' => $this->weight_checked,
            'photo_documented' => $this->photo_documented,
            'notes' => $this->notes,
            'goods_items' => GoodsItemResource::collection($this->whenLoaded('goods_items')),
        ];
    }
}