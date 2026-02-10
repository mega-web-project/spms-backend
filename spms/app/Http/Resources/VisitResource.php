<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VisitResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'visit_type' => $this->visit_type,
            'status' => $this->status,
            'checked_in_at' => $this->checked_in_at,
            'checked_out_at' => $this->checked_out_at,
            'vehicle' => $this->whenLoaded('vehicle'),
            'driver' => $this->whenLoaded('driver'),
            'goods_items' => GoodsItemResource::collection($this->whenLoaded('goods_items')),
            'members' => $this->when(
                $this->visit_type === 'visitors',
                $this->members
            ),

            // visitor
            'visitor_id' => $this->when(
            $this->visit_type === 'visitors',
            $this->visitor_id
        ),

            // vehicle
            'vehicle_id' => $this->when(
            $this->visit_type === 'vehicles',
            $this->vehicle_id
        ),
         'has_discrepancies' => $this->when(
            $this->visit_type === 'vehicles',
            $this->has_discrepancies
        ),

        'goods_verified' => $this->when(
            $this->visit_type === 'vehicles',
            $this->goods_verified
        ),
        'weight_checked' => $this->when(
            $this->visit_type === 'vehicles',
            $this->weight_checked
        ),
        'photo_documented' => $this->when(
            $this->visit_type === 'vehicles',
            $this->photo_documented
        ),
        'notes' => $this->when(
            $this->visit_type === 'vehicles',
            $this->notes
        ),

            'driver_id' => $this->when(
            $this->visit_type === 'vehicles',
            $this->driver_id
        ),

            'purpose' => $this->purpose ?? $this->purpose_of_visit,
            'assigned_bay' => $this->when(
            $this->visit_type === 'vehicles',
            $this->assigned_bay
        ),
        ];
    }
}
