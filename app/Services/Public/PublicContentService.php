<?php

namespace App\Services\Public;

use App\Models\Faq;
use App\Models\PortfolioProject;
use App\Models\ServiceRequestOption;
use App\Models\Testimonial;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PublicContentService
{
    public function portfolio(array $filters = []): LengthAwarePaginator
    {
        $perPage = min(max((int) ($filters['per_page'] ?? 12), 1), 48);

        $query = PortfolioProject::query()
            ->active()
            ->with([
                'technologies' => fn ($q) => $q->active(),
                'services' => fn ($q) => $q->public(),
            ])
            ->orderBy('sort_order')
            ->orderByDesc('completion_date');

        if (! empty($filters['featured']) && filter_var($filters['featured'], FILTER_VALIDATE_BOOLEAN)) {
            $query->featured();
        }

        return $query->paginate($perPage)->withQueryString();
    }

    public function findPortfolioBySlug(string $slug): PortfolioProject
    {
        return PortfolioProject::query()
            ->active()
            ->where('slug', $slug)
            ->with([
                'images',
                'technologies' => fn ($q) => $q->active(),
                'services' => fn ($q) => $q->public()->with('category'),
            ])
            ->firstOrFail();
    }

    public function testimonials(bool $featuredOnly = false)
    {
        $query = Testimonial::query()->active()->orderBy('sort_order');

        if ($featuredOnly) {
            $query->featured();
        }

        return $query->get();
    }

    public function faqs(bool $featuredOnly = false)
    {
        $query = Faq::query()->active()->orderBy('sort_order');

        if ($featuredOnly) {
            $query->featured();
        }

        return $query->get();
    }

    public function requestOptions(): array
    {
        $options = ServiceRequestOption::query()
            ->active()
            ->orderBy('sort_order')
            ->get()
            ->groupBy(fn (ServiceRequestOption $option) => $option->option_type->value);

        return [
            'project_types' => $options->get('PROJECT_TYPE', collect())->values(),
            'budget_ranges' => $options->get('BUDGET_RANGE', collect())->values(),
            'timelines' => $options->get('TIMELINE', collect())->values(),
            'contact_methods' => $options->get('CONTACT_METHOD', collect())->values(),
        ];
    }
}
