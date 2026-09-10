<?php

namespace App\Http\Resources\Public;

use App\Enums\PricingType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Service */
class ServiceCardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->localized_name,
            'short_description' => $this->localized_short_description,
            'icon' => $this->icon,
            'image' => $this->image ? asset('storage/'.$this->image) : null,
            'service_type' => $this->service_type?->value,
            'is_featured' => $this->is_featured,
            'category' => $this->whenLoaded('category', fn () => [
                'slug' => $this->category->slug,
                'name' => $this->category->localized_name,
                'icon' => $this->category->icon,
            ]),
            'pricing' => [
                'type' => $this->pricing_type?->value,
                'label' => $this->pricingLabel(),
                'starting_price' => $this->shouldShowPrice() ? (float) $this->starting_price : null,
                'currency' => $this->whenLoaded('currency', fn () => [
                    'code' => $this->currency?->code,
                    'symbol' => $this->currency?->symbol,
                ]),
            ],
            'technologies' => $this->whenLoaded('technologies', fn () => $this->technologies->map(fn ($tech) => [
                'slug' => $tech->slug,
                'name' => $tech->localized_name,
                'icon' => $tech->icon,
            ])->values()),
            'capabilities' => $this->whenLoaded('features', fn () => $this->features->take(4)->map(fn ($feature) => [
                'title' => $feature->localized_title,
                'icon' => $feature->icon,
            ])->values()),
        ];
    }

    protected function shouldShowPrice(): bool
    {
        return in_array($this->pricing_type, [PricingType::Fixed, PricingType::StartingFrom], true)
            && $this->starting_price !== null;
    }

    protected function pricingLabel(): string
    {
        return match ($this->pricing_type) {
            PricingType::Fixed => __('Fixed Price'),
            PricingType::StartingFrom => __('Starting From'),
            PricingType::Custom => __('Custom Pricing'),
            PricingType::ContactUs => __('Contact Us'),
            PricingType::QuoteRequired => __('Request a Quote'),
            default => __('Contact Us'),
        };
    }
}
