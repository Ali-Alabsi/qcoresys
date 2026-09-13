<?php

namespace App\Http\Resources\Public;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Faq */
class FaqResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'question' => $this->localized_question,
            'answer' => $this->localized_answer,
            'category' => $this->category,
        ];
    }
}
