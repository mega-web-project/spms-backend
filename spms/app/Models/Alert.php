<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Alert extends Model
{
    protected $fillable = [
        'type',
        'severity',
        'message',
        'entity_type',
        'entity_id',
        'resolved',
        'resolved_at',
    ];

    protected $casts = [
        'resolved' => 'boolean',
        'resolved_at' => 'datetime',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('resolved', false);
    }

    public static function createIfNotExists(array $payload): self
    {
        $type = $payload['type'] ?? null;
        $entityType = $payload['entity_type'] ?? null;
        $entityId = $payload['entity_id'] ?? null;

        if ($type && $entityType && $entityId) {
            $existing = self::query()
                ->where('type', $type)
                ->where('entity_type', $entityType)
                ->where('entity_id', $entityId)
                ->where('resolved', false)
                ->first();

            if ($existing) {
                return $existing;
            }
        }

        return self::create($payload);
    }
}
