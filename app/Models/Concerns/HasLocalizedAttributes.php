<?php

namespace App\Models\Concerns;

trait HasLocalizedAttributes
{
    public function localized(string $attribute, ?string $locale = null): mixed
    {
        $locale = $locale ?? app()->getLocale();
        $arabicKey = $attribute.'_ar';

        if ($locale === 'ar' && array_key_exists($arabicKey, $this->attributes) && filled($this->attributes[$arabicKey])) {
            return $this->attributes[$arabicKey];
        }

        return $this->attributes[$attribute] ?? null;
    }
}
