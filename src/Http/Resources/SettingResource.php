<?php

namespace Strata\Settings\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SettingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'key' => $this->key,
            'value' => $this->encrypted ? '***encrypted***' : $this->getCastedValue(),
            'type' => $this->type,
            'group' => $this->group,
            'label' => $this->label,
            'description' => $this->description,
            'is_public' => $this->is_public,
            'encrypted' => $this->encrypted,
            'order' => $this->order,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
