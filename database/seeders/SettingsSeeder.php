<?php

namespace Database\Seeders;

use App\Enums\DocumentSequenceKey;
use App\Enums\SettingType;
use App\Models\Account;
use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = Account::query()
            ->whereIn('account_code', ['111101', '111102', '1121', '2111', '4112', '4111', '4199', '5111', '5199'])
            ->pluck('id', 'account_code');

        $settings = [
            [
                'key' => 'company_name',
                'value' => 'QCoreSys',
                'type' => SettingType::String,
                'group' => 'general',
                'description' => 'Company legal name',
                'is_public' => true,
            ],
            [
                'key' => 'base_currency',
                'value' => 'USD',
                'type' => SettingType::String,
                'group' => 'general',
                'description' => 'Base currency code',
                'is_public' => true,
            ],
            [
                'key' => 'account_cash',
                'value' => (string) ($accounts['111101'] ?? ''),
                'type' => SettingType::Integer,
                'group' => 'accounting',
                'description' => 'Default cash account (Mohamed Visa USD)',
                'is_public' => false,
            ],
            [
                'key' => 'account_bank',
                'value' => (string) ($accounts['111102'] ?? ''),
                'type' => SettingType::Integer,
                'group' => 'accounting',
                'description' => 'Default bank / current account (Mohamed Current USD)',
                'is_public' => false,
            ],
            [
                'key' => 'account_ar',
                'value' => (string) ($accounts['1121'] ?? ''),
                'type' => SettingType::Integer,
                'group' => 'accounting',
                'description' => 'Accounts receivable USD',
                'is_public' => false,
            ],
            [
                'key' => 'account_ap',
                'value' => (string) ($accounts['2111'] ?? ''),
                'type' => SettingType::Integer,
                'group' => 'accounting',
                'description' => 'Accounts payable USD',
                'is_public' => false,
            ],
            [
                'key' => 'account_revenue_consulting',
                'value' => (string) ($accounts['4112'] ?? ''),
                'type' => SettingType::Integer,
                'group' => 'accounting',
                'description' => 'Consulting / banking integration revenue USD',
                'is_public' => false,
            ],
            [
                'key' => 'account_revenue_project',
                'value' => (string) ($accounts['4111'] ?? ''),
                'type' => SettingType::Integer,
                'group' => 'accounting',
                'description' => 'Software / project revenue USD',
                'is_public' => false,
            ],
            [
                'key' => 'account_expense_default',
                'value' => (string) ($accounts['5111'] ?? ''),
                'type' => SettingType::Integer,
                'group' => 'accounting',
                'description' => 'Default expense account (Hosting USD)',
                'is_public' => false,
            ],
            [
                'key' => 'account_fx_gain',
                'value' => (string) ($accounts['4199'] ?? ''),
                'type' => SettingType::Integer,
                'group' => 'accounting',
                'description' => 'Foreign exchange gain account (USD)',
                'is_public' => false,
            ],
            [
                'key' => 'account_fx_loss',
                'value' => (string) ($accounts['5199'] ?? ''),
                'type' => SettingType::Integer,
                'group' => 'accounting',
                'description' => 'Foreign exchange loss account (USD)',
                'is_public' => false,
            ],
        ];

        foreach (DocumentSequenceKey::cases() as $key) {
            $settings[] = [
                'key' => 'prefix_'.strtolower($key->name),
                'value' => $key->value,
                'type' => SettingType::String,
                'group' => 'documents',
                'description' => "Document prefix for {$key->name}",
                'is_public' => false,
            ];
        }

        $publicSettings = [
            ['company_description', 'Technology consulting and services for modern organizations, with deep expertise in banking and financial systems.', 'company', 'Company description (EN)'],
            ['company_description_en', 'Technology consulting and services for modern organizations, with deep expertise in banking and financial systems.', 'company', 'Company description English'],
            ['company_description_ar', 'استشارات وخدمات تقنية للمؤسسات الحديثة، مع خبرة عميقة في النظم المصرفية والمالية.', 'company', 'Company description Arabic'],
            ['company_email', 'info@qcoresys.com', 'company', 'Public email'],
            ['company_phone', '', 'company', 'Public phone'],
            ['company_whatsapp', '', 'company', 'Public WhatsApp'],
            ['company_address', '', 'company', 'Public address'],
            ['company_address_en', '', 'company', 'Public address English'],
            ['company_address_ar', '', 'company', 'Public address Arabic'],
            ['company_logo', 'brand/qcore-logo.png', 'company', 'Public logo path'],
            ['social_linkedin', '', 'company', 'LinkedIn URL'],
            ['social_twitter', '', 'company', 'Twitter URL'],
            ['social_facebook', '', 'company', 'Facebook URL'],
            ['social_instagram', '', 'company', 'Instagram URL'],
            ['social_youtube', '', 'company', 'YouTube URL'],
            ['hero_title_en', 'Technology Solutions & Consulting That Make a Real Difference', 'hero', 'Home hero title EN'],
            ['hero_title_ar', 'حلول تقنية واستشارات تصنع فرقًا حقيقيًا', 'hero', 'Home hero title AR'],
            ['hero_subtitle_en', 'We help organizations design, develop, and operate modern technology solutions—with strong expertise across banking, finance, and enterprise systems.', 'hero', 'Home hero subtitle EN'],
            ['hero_subtitle_ar', 'نساعد المؤسسات على تصميم وتطوير وتشغيل حلول تقنية حديثة—مع خبرة قوية في القطاع المصرفي والمالي وأنظمة المؤسسات.', 'hero', 'Home hero subtitle AR'],
            ['services_hero_title_en', 'Technology Services', 'hero', 'Services page hero EN'],
            ['services_hero_title_ar', 'الخدمات التقنية', 'hero', 'Services page hero AR'],
            ['services_hero_subtitle_en', 'Browse our portfolio—from banking and financial systems to software, cloud, and enterprise technology services.', 'hero', 'Services page subtitle EN'],
            ['services_hero_subtitle_ar', 'تصفح محفظة خدماتنا—من النظم المصرفية والمالية إلى البرمجيات والسحابة وخدمات تقنية المؤسسات.', 'hero', 'Services page subtitle AR'],
            ['trust_title_en', 'Technology Expertise Focused on Results', 'trust', 'Trust title EN'],
            ['trust_title_ar', 'خبرة تقنية تركز على النتائج', 'trust', 'Trust title AR'],
            ['trust_body_en', 'We partner with organizations across industries to design, integrate, and deliver production-ready technology—drawing on deep experience in banking and financial platforms.', 'trust', 'Trust body EN'],
            ['trust_body_ar', 'نشارك المؤسسات في مختلف القطاعات على تصميم وتكامل وتسليم حلول تقنية جاهزة للإنتاج—مستندين إلى خبرة عميقة في المنصات المصرفية والمالية.', 'trust', 'Trust body AR'],
            ['cta_title_en', 'Turn Your Idea Into Production-Ready Technology', 'cta', 'Final CTA title EN'],
            ['cta_title_ar', 'حوّل فكرتك إلى حل تقني جاهز للإنتاج', 'cta', 'Final CTA title AR'],
            ['cta_subtitle_en', 'From first consultation through design, development, and operations—with deep expertise in banking, finance, and enterprise systems.', 'cta', 'Final CTA subtitle EN'],
            ['cta_subtitle_ar', 'من الاستشارة الأولى حتى التصميم والتطوير والتشغيل—نرافقك بخبرة عميقة في الأنظمة المصرفية والمالية وأنظمة المؤسسات.', 'cta', 'Final CTA subtitle AR'],
            ['about_title_en', 'The QCoreSys Technology Platform', 'company', 'About title EN'],
            ['about_title_ar', 'منظومة QCoreSys التقنية', 'company', 'About title AR'],
            ['about_body_en', 'A specialized firm in precise financial and technology solutions, combining deep banking expertise with software engineering to help banks, exchange companies, and enterprises run their operations efficiently and securely.', 'company', 'About body EN'],
            ['about_body_ar', 'شركة متخصصة في الحلول المالية والتقنية الدقيقة، تجمع الخبرة المصرفية العميقة والهندسة البرمجية لتمكين البنوك وشركات الصرافة والمؤسسات من تشغيل عملياتها بكفاءة وأمان.', 'company', 'About body AR'],
            ['bank_details', "Bank: QCoreSys Bank\nAccount name: QCoreSys\nIBAN: SA00 0000 0000 0000 0000 0000\nSWIFT: QCOREXXX", 'documents', 'Bank details shown on quotations and invoices'],
            ['default_document_terms', "This document is valid for the stated period.\nPrices exclude any taxes unless otherwise noted.\nPayment is due according to the approved schedule.", 'documents', 'Default terms for quotations and invoices'],
        ];

        foreach ($publicSettings as [$key, $value, $group, $description]) {
            $settings[] = [
                'key' => $key,
                'value' => $value,
                'type' => SettingType::String,
                'group' => $group,
                'description' => $description,
                'is_public' => true,
            ];
        }

        foreach ($settings as $setting) {
            Setting::query()->updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
