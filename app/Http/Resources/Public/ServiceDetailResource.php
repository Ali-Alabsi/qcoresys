<?php

namespace App\Http\Resources\Public;

use App\Enums\PricingType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Service */
class ServiceDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->localized_name,
            'short_description' => $this->localized_short_description,
            'description' => $this->localized_description,
            'overview' => $this->localized_overview,
            'icon' => $this->icon,
            'image' => $this->image ? asset('storage/'.$this->image) : null,
            'banner_image' => $this->banner_image ? asset('storage/'.$this->banner_image) : null,
            'service_type' => $this->service_type?->value,
            'is_featured' => $this->is_featured,
            'meta' => [
                'title' => $this->localized_meta_title ?: $this->localized_name,
                'description' => $this->localized_meta_description ?: $this->localized_short_description,
            ],
            'category' => $this->whenLoaded('category', fn () => [
                'slug' => $this->category->slug,
                'name' => $this->category->localized_name,
                'description' => $this->category->localized_description,
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
            'features' => $this->whenLoaded('features', fn () => $this->features->map(fn ($feature) => [
                'title' => $feature->localized_title,
                'description' => $feature->localized_description,
                'icon' => $feature->icon,
            ])->values()),
            'benefits' => $this->whenLoaded('benefits', fn () => $this->benefits->map(fn ($benefit) => [
                'title' => $benefit->localized_title,
                'description' => $benefit->localized_description,
                'icon' => $benefit->icon,
            ])->values()),
            'process_steps' => $this->whenLoaded('processSteps', fn () => $this->processSteps->map(fn ($step) => [
                'step_number' => $step->step_number,
                'title' => $step->localized_title,
                'description' => $step->localized_description,
                'icon' => $step->icon,
            ])->values()),
            'faqs' => $this->whenLoaded('faqs', fn () => $this->faqs->map(fn ($faq) => [
                'question' => $faq->localized_question,
                'answer' => $faq->localized_answer,
            ])->values()),
            'portfolio_projects' => $this->whenLoaded('portfolioProjects', fn () => $this->portfolioProjects->map(fn ($project) => [
                'slug' => $project->slug,
                'title' => $project->localized_title,
                'short_description' => $project->localized_short_description,
                'industry' => $project->localized_industry,
                'featured_image' => $project->featured_image ? asset('storage/'.$project->featured_image) : null,
                'technologies' => $project->relationLoaded('technologies')
                    ? $project->technologies->map(fn ($t) => $t->localized_name)->values()
                    : [],
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
