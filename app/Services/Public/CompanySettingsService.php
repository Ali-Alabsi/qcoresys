<?php

namespace App\Services\Public;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class CompanySettingsService
{
    public const CACHE_KEY = 'public.company.settings';

    public function getPublicSettings(): array
    {
        return Cache::remember(self::CACHE_KEY, now()->addMinutes(30), function () {
            return Setting::query()
                ->public()
                ->whereIn('group', ['general', 'company', 'site', 'hero', 'trust', 'cta'])
                ->get()
                ->mapWithKeys(fn (Setting $setting) => [$setting->key => $setting->value])
                ->all();
        });
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->getPublicSettings()[$key] ?? $default;
    }

    public function forgetCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
