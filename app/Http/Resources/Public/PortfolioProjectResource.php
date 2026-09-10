<?php

namespace App\Http\Resources\Public;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\PortfolioProject */
class PortfolioProjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'slug' => $this->slug,
            'title' => $this->localized_title,
            'short_description' => $this->localized_short_description,
            'description' => $this->when($request->routeIs('api.public.portfolio.show') || $request->routeIs('portfolio.show'), $this->localized_description),
            'client_name' => $this->client_name,
            'industry' => $this->localized_industry,
            'featured_image' => $this->featured_image ? asset('storage/'.$this->featured_image) : null,
            'completion_date' => optional($this->completion_date)?->toDateString(),
            'is_featured' => $this->is_featured,
            'technologies' => $this->whenLoaded('technologies', fn () => $this->technologies->map(fn ($t) => [
                'slug' => $t->slug,
                'name' => $t->localized_name,
            ])->values()),
            'services' => $this->whenLoaded('services', fn () => $this->services->map(fn ($s) => [
                'slug' => $s->slug,
                'name' => $s->localized_name,
            ])->values()),
            'images' => $this->whenLoaded('images', fn () => $this->images->map(fn ($image) => [
                'url' => asset('storage/'.$image->image_path),
                'alt' => $image->localized_alt_text,
            ])->values()),
        ];
    }
}
