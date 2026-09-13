<?php

namespace App\Http\Resources\Public;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\ServiceCategory */
class ServiceCategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'slug' => $this->slug,
            'code' => $this->code,
            'name' => $this->localized_name,
            'description' => $this->localized_description,
            'icon' => $this->icon,
            'sort_order' => $this->sort_order,
        ];
    }
}
