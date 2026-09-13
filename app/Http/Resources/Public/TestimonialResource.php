<?php

namespace App\Http\Resources\Public;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Testimonial */
class TestimonialResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->localized_name,
            'company_name' => $this->localized_company_name,
            'position' => $this->localized_position,
            'content' => $this->localized_content,
            'avatar' => $this->avatar ? asset('storage/'.$this->avatar) : null,
            'rating' => $this->rating,
        ];
    }
}
