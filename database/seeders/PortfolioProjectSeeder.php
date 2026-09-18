<?php

namespace Database\Seeders;

use App\Models\PortfolioProject;
use App\Models\Service;
use Illuminate\Database\Seeder;

class PortfolioProjectSeeder extends Seeder
{
    public function run(): void
    {
        $projects = [
            [
                'slug' => 'core-banking-rollout',
                'title' => 'Core Banking Rollout',
                'title_ar' => 'إطلاق النظام المصرفي المركزي',
                'short_description' => 'End-to-end core banking implementation for a regional financial institution.',
                'short_description_ar' => 'تنفيذ نظام مصرفي مركزي متكامل لمؤسسة مالية إقليمية.',
                'description' => 'Configured branches, chart of accounts templates, FX/remittance workflows, and go-live support.',
                'description_ar' => 'تهيئة الفروع وقوالب دليل الحسابات ودورات الصرافة/الحوالات ودعم الإطلاق.',
                'client_name' => 'Regional Bank',
                'industry' => 'Banking',
                'industry_ar' => 'الخدمات المصرفية',
                'completion_date' => '2025-06-15',
                'is_featured' => true,
                'is_active' => true,
                'sort_order' => 1,
                'services' => ['core-banking-solutions', 'go-live-sla-support'],
            ],
            [
                'slug' => 'enterprise-web-portal',
                'title' => 'Enterprise Customer Portal',
                'title_ar' => 'بوابة عملاء مؤسسية',
                'short_description' => 'Secure self-service portal for customers, requests, and document tracking.',
                'short_description_ar' => 'بوابة ذاتية آمنة للعملاء والطلبات ومتابعة المستندات.',
                'description' => 'Built bilingual dashboards, request workflows, and document visibility for portal users.',
                'description_ar' => 'بناء لوحات ثنائية اللغة ودورات الطلبات وعرض المستندات لمستخدمي البوابة.',
                'client_name' => 'Demo Client Co.',
                'industry' => 'Software',
                'industry_ar' => 'البرمجيات',
                'completion_date' => '2025-11-01',
                'is_featured' => true,
                'is_active' => true,
                'sort_order' => 2,
                'services' => ['web-application-development', 'custom-fullstack-development'],
            ],
            [
                'slug' => 'smart-parking-platform',
                'title' => 'Smart Parking Platform',
                'title_ar' => 'منصة المواقف الذكية',
                'short_description' => 'IoT-enabled parking operations with real-time occupancy and payments.',
                'short_description_ar' => 'تشغيل مواقف مدعوم بإنترنت الأشياء مع الإشغال اللحظي والمدفوعات.',
                'description' => 'Delivered occupancy sensors integration, operator console, and customer mobile flows.',
                'description_ar' => 'تكامل حساسات الإشغال ووحدة المشغّل وتدفقات تطبيق العميل.',
                'client_name' => 'City Operations',
                'industry' => 'Smart City',
                'industry_ar' => 'المدن الذكية',
                'completion_date' => '2024-12-20',
                'is_featured' => false,
                'is_active' => true,
                'sort_order' => 3,
                'services' => ['smart-parking-solutions', 'mobile-application-development'],
            ],
        ];

        foreach ($projects as $project) {
            $serviceSlugs = $project['services'];
            unset($project['services']);

            $record = PortfolioProject::withTrashed()->updateOrCreate(
                ['slug' => $project['slug']],
                [...$project, 'deleted_at' => null]
            );

            if ($record->trashed()) {
                $record->restore();
            }

            $serviceIds = Service::query()
                ->whereIn('slug', $serviceSlugs)
                ->pluck('id')
                ->all();

            if ($serviceIds !== []) {
                $record->services()->sync($serviceIds);
            }
        }
    }
}
