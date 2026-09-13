<?php

namespace App\Services\Public;

use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Technology;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

class PublicCatalogService
{
    public function categories()
    {
        return Cache::remember('public.service_categories.'.app()->getLocale(), now()->addMinutes(15), function () {
            return ServiceCategory::query()
                ->public()
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();
        });
    }

    public function technologies()
    {
        return Cache::remember('public.technologies.'.app()->getLocale(), now()->addMinutes(15), function () {
            return Technology::query()
                ->active()
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();
        });
    }

    public function listServices(array $filters = []): LengthAwarePaginator
    {
        $perPage = min(max((int) ($filters['per_page'] ?? 12), 1), 48);

        $query = Service::query()
            ->public()
            ->with([
                'category:id,slug,name,name_ar,icon',
                'currency:id,code,name,symbol',
                'technologies' => fn ($q) => $q->active()->orderByPivot('sort_order'),
                'features' => fn ($q) => $q->active()->orderBy('sort_order')->limit(6),
            ])
            ->orderBy('sort_order')
            ->orderBy('name');

        $this->applyFilters($query, $filters);

        return $query->paginate($perPage)->withQueryString();
    }

    public function forgetCatalogCache(): void
    {
        foreach (['ar', 'en'] as $locale) {
            Cache::forget('public.service_categories.'.$locale);
            Cache::forget('public.technologies.'.$locale);
        }
    }

    public function featuredServices(int $limit = 6)
    {
        return Service::query()
            ->public()
            ->featured()
            ->with([
                'category:id,slug,name,name_ar,icon',
                'currency:id,code,name,symbol',
                'technologies' => fn ($q) => $q->active()->orderByPivot('sort_order'),
            ])
            ->orderBy('sort_order')
            ->limit($limit)
            ->get();
    }

    public function spotlightServices(int $limit = 6)
    {
        $relations = [
            'category:id,slug,name,name_ar,icon',
            'currency:id,code,name,symbol',
            'technologies' => fn ($q) => $q->active()->orderByPivot('sort_order'),
        ];

        $featured = Service::query()
            ->public()
            ->featured()
            ->with($relations)
            ->orderBy('sort_order')
            ->limit($limit)
            ->get();

        if ($featured->count() >= $limit) {
            return $featured;
        }

        $extra = Service::query()
            ->public()
            ->whereNotIn('id', $featured->pluck('id'))
            ->with($relations)
            ->orderBy('sort_order')
            ->limit($limit - $featured->count())
            ->get();

        return $featured->concat($extra)->values();
    }

    public function findBySlug(string $slug): Service
    {
        return Service::query()
            ->public()
            ->where('slug', $slug)
            ->with([
                'category:id,slug,name,name_ar,icon,description,description_ar',
                'currency:id,code,name,symbol',
                'technologies' => fn ($q) => $q->active()->orderByPivot('sort_order'),
                'features' => fn ($q) => $q->active()->orderBy('sort_order'),
                'benefits' => fn ($q) => $q->active()->orderBy('sort_order'),
                'processSteps' => fn ($q) => $q->active()->orderBy('sort_order'),
                'faqs' => fn ($q) => $q->active()->orderByPivot('sort_order'),
            ])
            ->firstOrFail();
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        if (! empty($filters['featured']) && filter_var($filters['featured'], FILTER_VALIDATE_BOOLEAN)) {
            $query->featured();
        }

        if (! empty($filters['category'])) {
            $category = $filters['category'];
            $query->whereHas('category', function (Builder $q) use ($category) {
                $q->where('slug', $category)->orWhere('code', $category);
            });
        }

        if (! empty($filters['technology'])) {
            $technology = $filters['technology'];
            $query->whereHas('technologies', function (Builder $q) use ($technology) {
                $q->where('slug', $technology);
            });
        }

        if (! empty($filters['service_type'])) {
            $query->where('service_type', $filters['service_type']);
        }

        if (! empty($filters['pricing_type'])) {
            $query->where('pricing_type', $filters['pricing_type']);
        }

        if (! empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function (Builder $q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('name_ar', 'like', "%{$search}%")
                    ->orWhere('short_description', 'like', "%{$search}%")
                    ->orWhere('short_description_ar', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('description_ar', 'like', "%{$search}%")
                    ->orWhereHas('category', function (Builder $cq) use ($search) {
                        $cq->where('name', 'like', "%{$search}%")
                            ->orWhere('name_ar', 'like', "%{$search}%");
                    })
                    ->orWhereHas('technologies', function (Builder $tq) use ($search) {
                        $tq->where('name', 'like', "%{$search}%")
                            ->orWhere('name_ar', 'like', "%{$search}%");
                    })
                    ->orWhereHas('features', function (Builder $fq) use ($search) {
                        $fq->where('title', 'like', "%{$search}%")
                            ->orWhere('title_ar', 'like', "%{$search}%");
                    });
            });
        }
    }
}
